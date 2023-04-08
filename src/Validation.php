<?php declare(strict_types=1);

namespace Asterios\Core;

use Asterios\Core\Exception\ValidationDomainException;
use Asterios\Core\Exception\ValidationUnexpectedValueException;

/**
 * This class handle different types of validation.
 * You can use your own error messages, or just leave it blank to get default error messages.
 *
 * Usage:
 * $input = 'info.f.f.f.f.@ffff.deeeee';
 * $validate = new Validation();
 * $response = $validate->run($input, 'email', 'email|required');
 *
 * if ($response === false)
 * {
 *    Debug::dump($validate->get_errors());
 * }
 *
 */
class Validation
{

    public const RULE_ALPHA = 'alpha';
    public const RULE_ALPHA_NUMERIC = 'alpha_numeric';
    public const RULE_NUMERIC = 'numeric';
    public const RULE_INTEGER = 'integer';
    public const RULE_FLOAT = 'float';
    public const RULE_EMAIL = 'email';
    public const RULE_REQUIRED = 'required';
    public const RULE_MIN_LENGTH = 'min_length';
    public const RULE_MAX_LENGTH = 'max_length';
    public const RULE_EXACT_LENGTH = 'exact_length';
    public const RULE_REGEX = 'regex';

    /**
     * @var  array  contains available validation rules
     */
    protected $_rules = [
        self::RULE_ALPHA,
        self::RULE_ALPHA_NUMERIC,
        self::RULE_NUMERIC,
        self::RULE_INTEGER,
        self::RULE_FLOAT,
        self::RULE_EMAIL,
        self::RULE_REQUIRED,
        self::RULE_MIN_LENGTH,
        self::RULE_MAX_LENGTH,
        self::RULE_EXACT_LENGTH,
        self::RULE_REGEX,
    ];

    /**
     * @var  array  contains default error messages
     */
    protected $_default_error_messages = [
        self::RULE_ALPHA => 'Given value is not alpha.',
        self::RULE_ALPHA_NUMERIC => 'Given value is not alpha-numeric.',
        self::RULE_NUMERIC => 'Given value is not numeric.',
        self::RULE_INTEGER => 'Given value is not an integer.',
        self::RULE_FLOAT => 'Given value is not an float.',
        self::RULE_EMAIL => 'Given value is not a valid e-mail address.',
        self::RULE_REQUIRED => 'Given value is required.',
        self::RULE_MIN_LENGTH => 'Given value is to short.',
        self::RULE_MAX_LENGTH => 'Given value is to long.',
        self::RULE_EXACT_LENGTH => 'Given value has not the exact length.',
        self::RULE_REGEX => 'Your regular expression does not match the subject string.',
    ];

    protected $_required_parameter_for_rule = [
        self::RULE_MIN_LENGTH,
        self::RULE_MIN_LENGTH,
        self::RULE_EXACT_LENGTH,
        //       self::RULE_REGEX,
    ];

    private $error_messages = [];

    private $error_messages_helper = [];

    /**
     * Run validation
     *
     * @param string $input Value that should be validate
     * @param string $field Name of the field that contains given value
     * @param string|array $rules The set of validation rules with (optional) rule parameters. Rules are separated with a pipe symbol (|), or as a array of rules.
     * @param null|string $regex Regex pattern (optional)
     * @param mixed $optional_value Optional value. Will be used by several validation methods like min_length.
     * @param mixed $error_message Custom error message. Messages are separated with a pipe symbol (|), or as a array of Messages. If not set, default message will be used.
     * @return boolean
     */
    public function run(string $input, string $field, $rules, ?string $regex = null, $optional_value = null, $error_message = null): bool
    {
        if (!is_array($rules))
        {
            $rules = explode('|', $rules);
        }

        if ($error_message !== null)
        {
            if (!is_array($error_message))
            {
                $error_message = explode('|', $error_message);
            }

            $this->error_messages_helper = array_combine($rules, $error_message);
        }

        foreach ($rules as $rule)
        {
            if (!$this->check_rule($rule))
            {
                return false;
            }

            if (!$this->check_required_parameters($rule, $optional_value))
            {
                return false;
            }

            if ($rule === self::RULE_ALPHA && !$this->_validate_alpha($input))
            {
                $this->set_error_message($field, $rule, $this->error_messages_helper);
            }
            if ($rule === self::RULE_ALPHA_NUMERIC && !$this->_validate_alpha_numeric($input))
            {
                $this->set_error_message($field, $rule, $this->error_messages_helper);
            }
            if ($rule === self::RULE_NUMERIC && !$this->_validate_numeric($input))
            {
                $this->set_error_message($field, $rule, $this->error_messages_helper);
            }
            if ($rule === self::RULE_INTEGER && $this->_validate_integer($input) === false)
            {
                $this->set_error_message($field, $rule, $this->error_messages_helper);
            }
            if ($rule === self::RULE_FLOAT && $this->_validate_float($input) === false)
            {
                $this->set_error_message($field, $rule, $this->error_messages_helper);
            }
            if ($rule === self::RULE_EMAIL && $this->_validate_email($input) === false)
            {
                $this->set_error_message($field, $rule, $this->error_messages_helper);
            }
            if ($rule === self::RULE_REQUIRED && $this->_validate_required($input) === false)
            {
                $this->set_error_message($field, $rule, $this->error_messages_helper);
            }
            if ($rule === self::RULE_MIN_LENGTH)
            {
                if ($this->_validate_integer($optional_value) === false)
                {
                    $this->set_error_message('optional_value', self::RULE_INTEGER, $this->error_messages_helper);
                }
                else
                {
                    if ($this->_validate_min_length($input, $optional_value) === false)
                    {
                        $this->set_error_message($field, $rule, $this->error_messages_helper);
                    }
                }
            }
            if ($rule === self::RULE_MAX_LENGTH)
            {
                if ($this->_validate_integer($optional_value) === false)
                {
                    $this->set_error_message('optional_value', self::RULE_INTEGER, $this->error_messages_helper);
                }
                else
                {
                    if ($this->_validate_max_length($input, $optional_value) === false)
                    {
                        $this->set_error_message($field, $rule, $this->error_messages_helper);
                    }
                }
            }
            if ($rule === self::RULE_EXACT_LENGTH)
            {
                if ($this->_validate_integer($optional_value) === false)
                {
                    $this->set_error_message('optional_value', self::RULE_INTEGER, $this->error_messages_helper);
                }
                else
                {
                    if ($this->_validate_exact_length($input, $optional_value) === false)
                    {
                        $this->set_error_message($field, $rule, $this->error_messages_helper);
                    }
                }
            }
            if ($rule === self::RULE_REGEX && !$this->_validate_regex($input, $regex))
            {
                $this->set_error_message($field, $rule, $this->error_messages_helper);
            }
        }

        if (!empty($this->error_messages))
        {
            return false;
        }

        return true;
    }

    private function _validate_alpha(string $input): bool
    {
        return ctype_alpha($input);
    }

    private function _validate_alpha_numeric(string $input): bool
    {
        return ctype_alnum($input);
    }

    private function _validate_numeric(string $input): bool
    {
        return is_numeric($input);
    }

    /**
     * @param mixed $input
     * @return boolean
     */
    private function _validate_integer($input): bool
    {
        return is_int($input);
    }

    /**
     * Method for float validation
     * @param mixed $input
     * @return boolean
     */
    private function _validate_float($input): bool
    {
        return is_float($input);
    }

    private function _validate_email(string $input): bool
    {
        return preg_match("/[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/",
                $input) === 1;
    }

    /**
     * Method for required validation
     * @param mixed $input
     * @return boolean
     */
    private function _validate_required($input): bool
    {
        if (empty($input))
        {
            return false;
        }

        return true;
    }

    private function _validate_min_length(string $input, int $length): bool
    {
        return !(strlen($input) < $length);
    }

    private function _validate_max_length(string $input, int $length): bool
    {
        return !(strlen($input) > $length);
    }

    private function _validate_exact_length(string $input, int $length): bool
    {
        return !(strlen($input) !== $length);
    }

    private function _validate_regex(string $input, string $pattern): bool
    {
        return (bool)preg_match($pattern, $input);
    }

    public function get_errors(): \stdClass
    {
        return (object)$this->error_messages;
    }

    private function set_error_message(string $field, string $rule, array $message): void
    {
        if (empty($message))
        {
            $this->error_messages[$field][$rule] = $this->_default_error_messages[$rule];

            return;
        }

        $this->error_messages[$field][$rule] = $message[$rule];
    }

    private function check_rule(string $rule): bool
    {
        try
        {
            if (!in_array($rule, $this->_rules, true))
            {
                throw new ValidationUnexpectedValueException('Given rule "' . $rule . '" is not supported!');
            }
        } catch (ValidationUnexpectedValueException $e)
        {
            $this->error_messages['fatal'] = $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * @param string $rule
     * @param mixed $optional_value
     * @return bool
     */
    private function check_required_parameters(string $rule, $optional_value): bool
    {
        try
        {
            if ($optional_value === null && in_array($rule, $this->_required_parameter_for_rule))
            {
                throw new ValidationDomainException('Given rule "' . $rule . '" need optional parameter!');
            }
        } catch (ValidationDomainException $e)
        {
            $this->error_messages['fatal'] = $e->getMessage();

            return false;
        }

        return true;
    }
}
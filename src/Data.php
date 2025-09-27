<?php

declare(strict_types=1);

namespace Asterios\Core;

use ReflectionClass;
use ReflectionProperty;

abstract class Data
{
    protected static array $requiredFields = [];

    protected static array $validationRules = [];

    public function toArray(): array
    {
        $data = [];

        foreach (get_object_vars($this) as $key => $value) {
            if ($value instanceof self) {
                $data[$key] = $value->toArray();
            } elseif (is_array($value)) {
                $data[$key] = array_map(
                    static fn($item) => $item instanceof Data ? $item->toArray() : $item,
                    $value
                );
            } else {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function fromArray(array $data): static
    {
        $ref = new ReflectionClass($this);

        foreach ($ref->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $name = $prop->getName();
            if (!array_key_exists($name, $data) || $data[$name] === null) continue;

            $type = $prop->getType()?->getName();
            $value = $data[$name];

            if ($type !== null && is_subclass_of($type, self::class) && is_array($value)) {
                $this->{$name} = new $type($value);
                continue;
            }

            if ($type === 'array' && is_array($value)) {
                $docComment = $prop->getDocComment() ?: '';
                $dtoClass = $this->extractDataClassFromVarDoc($docComment, $prop);

                if ($dtoClass !== null && class_exists($dtoClass) && is_subclass_of($dtoClass, self::class)) {
                    $this->{$name} = array_map(
                        static fn($item) => new $dtoClass($item),
                        $value
                    );
                } else {
                    $this->{$name} = $value;
                }
                continue;
            }

            $this->{$name} = $value;
        }

        return $this;
    }

    private function extractDataClassFromVarDoc(string $docComment, ReflectionProperty $prop): ?string
    {
        if (preg_match('/@var\s+([\\\\\w]+)\[\]/', $docComment, $matches)) {
            $className = $matches[1];
            if ($className[0] !== '\\') {
                $className = $prop->getDeclaringClass()->getNamespaceName() . '\\' . $className;
            }
            return $className;
        }
        return null;
    }

    public function validate(): array
    {
        $errors = [];

        foreach (static::$requiredFields as $field) {
            if (!property_exists($this, $field) || $this->{$field} === null) {
                $errors[] = "Field '$field' is required.";
            }
        }

        foreach (static::$validationRules as $field => $rules) {
            $value = $this->{$field} ?? null;

            foreach ($rules as $rule) {
                switch ($rule) {
                    case 'required':
                        if ($value === null) {
                            $errors[] = "Field '$field' is required.";
                        }
                        break;

                    case 'not_empty':
                        if (is_string($value) && trim($value) === '') {
                            $errors[] = "Field '$field' must not be empty.";
                        }
                        break;

                    case 'email':
                        if ($value !== null && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[] = "Field '$field' must be a valid email address.";
                        }
                        break;

                    case 'numeric':
                        if ($value !== null && !is_numeric($value)) {
                            $errors[] = "Field '$field' must be numeric.";
                        }
                        break;

                    default:
                        if (str_starts_with($rule, 'regex:')) {
                            $pattern = substr($rule, 6);
                            if ($value !== null && !preg_match($pattern, (string)$value)) {
                                $errors[] = "Field '$field' does not match pattern $pattern.";
                            }
                        }
                        break;
                }
            }
        }

        return $errors;
    }
}

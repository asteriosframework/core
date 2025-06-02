<?php declare(strict_types=1);

namespace Asterios\Core;

use Asterios\Core\Exception\ViewTemplateAccessException;

class View
{
    /**
     * @var  string  The template name
     */
    protected $template;

    /**
     * @var  string  The template extension
     */
    protected $extension;

    /**
     * @var  string  The template file
     */
    protected $template_file;

    /**
     * @throws Exception\ConfigLoadException
     * @throws ViewTemplateAccessException
     */
    public static function forge(string $template): View
    {
        return new self($template);
    }

    /**
     * @throws Exception\ConfigLoadException
     * @throws ViewTemplateAccessException
     */
    protected function __construct(string $template)
    {
        $this->extension = Asterios::config('template.extension');
        $this->template = $template;
        $this->set_template_file();

        try
        {
            $this->render();
        }
        catch (ViewTemplateAccessException $e)
        {
            $this->view_error_tpl(404);
        }
    }

    /**
     * View error template if given template could not be found
     * @param int $http_error
     * @throws ViewTemplateAccessException
     */
    private function view_error_tpl(int $http_error): void
    {
        if (!file_exists($this->get_tpl_path() . $http_error . '.' . $this->extension))
        {
            ob_end_clean();
            throw new ViewTemplateAccessException('FATAL ERROR: Could not load error-template "' . $http_error . '"!');
        }
        else
        {
            include $this->get_tpl_path() . $http_error . '.' . $this->extension;
        }
    }

    /**
     * Captures the output that is generated when a view is included.
     * The view data will be extracted to make local variables.
     *
     *     $output = $this->process_file();
     * @return false|string
     * @throws ViewTemplateAccessException
     */
    protected function process_template(bool $file_override = false)
    {
        $clean_room = function ($__file_name) {

            // Capture the view output
            ob_start();

            try
            {
                if (!file_exists($__file_name))
                {
                    throw new ViewTemplateAccessException('404');
                }
                else
                {
                    include $__file_name;
                }
            }
            catch (ViewTemplateAccessException $e)
            {
                $this->view_error_tpl(404);
            }

            // Get the captured output and close the buffer
            return ob_get_clean();
        };

        // import and process the view file
        return $clean_room($file_override ?: $this->template_file);
    }

    private function set_template_file(): void
    {
        $this->template_file = $this->get_tpl_path() . $this->template . '.' . $this->extension;
    }

    /**
     * @throws ViewTemplateAccessException
     */
    public function render(): void
    {
        echo $this->process_template();
    }

    /**
     * @return string
     */
    protected function get_tpl_path(): string
    {
        return Config::get_memory('TPLPATH', '');
    }
}

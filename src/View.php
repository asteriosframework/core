<?php declare(strict_types=1);

namespace Asterios\Core;

use Asterios\Core\Exception\ConfigLoadException;
use Asterios\Core\Exception\EnvException;
use Asterios\Core\Exception\EnvLoadException;
use Asterios\Core\Exception\ViewTemplateAccessException;

class View
{
    protected string $template;
    protected string $templateFile;
    protected bool $autoRender;
    protected array $data = [];
    protected string $envFile = '.env';

    protected ?Env $env = null;

    /**
     * @param string $template
     * @param bool $autoRender
     * @param array $data
     * @param string $envFile
     * @return View
     * @throws ConfigLoadException
     * @throws ViewTemplateAccessException
     */
    public static function forge(string $template, bool $autoRender = true, array $data = [], string $envFile = '.env'): View
    {
        return new self($template, $autoRender, $data, $envFile);
    }

    /**
     * @param string $template
     * @param bool $autoRender
     * @param array $data
     * @param string $envFile
     * @throws ConfigLoadException
     * @throws ViewTemplateAccessException
     */
    protected function __construct(string $template, bool $autoRender = true, array $data = [], string $envFile = '.env')
    {
        $this->envFile = Asterios::getBasePath() . DIRECTORY_SEPARATOR . $envFile;

        if (null === $this->env)
        {
            $this->env = new Env($this->envFile);
        }

        $this->template = $template;
        $this->data = $data;
        $this->autoRender = $autoRender;
        $this->setTemplateFile();

        if ($this->autoRender)
        {
            try
            {
                $this->render();
            }
            catch (ViewTemplateAccessException)
            {
                $this->viewErrorTemplate();
            }
        }
    }

    /**
     * @return void
     * @throws ViewTemplateAccessException
     */
    private function viewErrorTemplate(): void
    {
        $error_template = $this->getTemplatePath() . 404 . '.' . $this->getTemplateExtension();

        if (!file_exists($error_template))
        {
            ob_end_clean();
            throw new ViewTemplateAccessException('FATAL ERROR: Could not load error-template "' . 404 . '"!');
        }

        include $error_template;
    }

    /**
     * @param string $overrideFile
     * @return string|false
     * @throws ViewTemplateAccessException
     */
    protected function processTemplate(string $overrideFile = ''): string|false
    {
        $data = $this->data;

        $cleanRoom = function ($templateFile) use ($data) {
            extract($data, EXTR_SKIP);
            ob_start();

            try
            {
                if (!file_exists($templateFile))
                {
                    throw new ViewTemplateAccessException('404');
                }
                include $templateFile;
            }
            catch (ViewTemplateAccessException)
            {
                $this->viewErrorTemplate();
            }

            return ob_get_clean();
        };

        return $cleanRoom($overrideFile ?: $this->templateFile);
    }

    /**
     * @return void
     * @throws ViewTemplateAccessException
     */
    private function setTemplateFile(): void
    {
        $this->templateFile = $this->getTemplatePath() . $this->template . '.' . $this->getTemplateExtension();
    }

    /**
     * @return string
     * @throws ViewTemplateAccessException
     */
    public function renderAsString(): string
    {
        return $this->processTemplate() ?: '';
    }

    /**
     * @return void
     * @throws ViewTemplateAccessException
     */
    public function render(): void
    {
        echo $this->renderAsString();
    }

    /**
     * @return string
     * @throws ViewTemplateAccessException
     */
    protected function getTemplatePath(): string
    {
        $templatePath = $this->getEnvData('TEMPLATE_PATH');

        return $templatePath ? $this->getProtectedPath() . $templatePath : '';
    }

    /**
     * @return string
     * @throws ViewTemplateAccessException
     */
    protected function getTemplateExtension(): string
    {
        return $this->getEnvData('TEMPLATE_EXTENSION');
    }

    /**
     * @param string $key
     * @return string
     * @throws ViewTemplateAccessException
     */
    protected function getEnvData(string $key): string
    {
        try
        {
            return $this->env->get($key);
        }
        catch (EnvException|EnvLoadException $e)
        {
            throw new ViewTemplateAccessException($e->getMessage());
        }
    }

    /**
     * @return string
     */
    protected function getProtectedPath(): string
    {
        return Asterios::getBasePath();
    }
}

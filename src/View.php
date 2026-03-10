<?php declare(strict_types=1);

namespace Asterios\Core;

use Asterios\Core\Exception\EnvException;
use Asterios\Core\Exception\EnvLoadException;
use Asterios\Core\Exception\ViewTemplateAccessException;
use Asterios\Core\View\Twig\TwigManager;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class View
{
    protected string $template;
    protected bool $autoRender;
    protected array $data = [];
    protected string $envFile = '.env';

    protected ?Env $env = null;
    protected ?Environment $twig = null;

    public static function forge(string $template, bool $autoRender = true, array $data = [], string $envFile = '.env'): self
    {
        return new self($template, $autoRender, $data, $envFile);
    }

    protected function __construct(string $template, bool $autoRender = true, array $data = [], string $envFile = '.env')
    {
        $this->envFile = Asterios::getBasePath() . DIRECTORY_SEPARATOR . $envFile;

        if ($this->env === null)
        {
            $this->env = new Env($this->envFile);
        }

        $this->template = $template;
        $this->data = $data;
        $this->autoRender = $autoRender;

        $this->twig = TwigManager::getTwig($this->env);

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

    protected function processTemplate(string $override = ''): string
    {
        $template = $override ?: $this->resolveTemplate();

        try
        {
            return $this->twig->render($template, $this->data);
        }
        catch (LoaderError|RuntimeError|SyntaxError $e)
        {
            throw new ViewTemplateAccessException($e->getMessage());
        }
    }

    protected function resolveTemplate(): string
    {
        if (str_contains($this->template, '::'))
        {
            [$namespace, $view] = explode('::', $this->template);

            return "@$namespace/$view." . $this->getTemplateExtension();
        }

        return $this->template . '.' . $this->getTemplateExtension();
    }

    private function viewErrorTemplate(): void
    {
        try
        {
            echo $this->twig->render('404.' . $this->getTemplateExtension());
        }
        catch (\Exception)
        {
            throw new ViewTemplateAccessException(
                'FATAL ERROR: Could not load error-template "404"!'
            );
        }
    }

    public function renderAsString(): string
    {
        return $this->processTemplate();
    }

    public function render(): void
    {
        echo $this->renderAsString();
    }

    protected function getTemplateExtension(): string
    {
        return $this->getEnvData('TEMPLATE_EXTENSION');
    }

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
}
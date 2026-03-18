<?php declare(strict_types=1);

namespace Asterios\Core;

use Asterios\Core\Exception\EnvException;
use Asterios\Core\Exception\EnvLoadException;
use Asterios\Core\Exception\ViewTemplateAccessException;
use Asterios\Core\View\Twig\TwigManager;
use Twig\Environment;

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
            $this->render();
        }
    }

    /**
     * Haupt-Template Verarbeitung
     */
    protected function processTemplate(): string
    {
        $twigTemplate = $this->resolveTwigTemplate();
        $phpTemplate  = $this->resolvePhpTemplate();

        if ($twigTemplate && file_exists($twigTemplate['path']))
        {
            return $this->renderTwig($twigTemplate['name']);
        }

        if ($phpTemplate && file_exists($phpTemplate))
        {
            return $this->renderPhp($phpTemplate);
        }

        throw new ViewTemplateAccessException('Template not found: ' . $this->template);
    }

    /**
     * Twig Render
     */
    protected function renderTwig(string $template): string
    {
        return $this->twig->render($template, $this->data);
    }

    /**
     * Alte PHP Engine (Fallback)
     */
    protected function renderPhp(string $templateFile): string
    {
        $data = $this->data;

        $cleanRoom = static function ($templateFile) use ($data) {
            extract($data, EXTR_SKIP);

            ob_start();
            include $templateFile;
            return ob_get_clean();
        };

        return $cleanRoom($templateFile);
    }

    /**
     * Twig Template Pfad
     */
    protected function resolveTwigTemplate(): ?array
    {
        $extension = $this->getEnvData('TEMPLATE_EXTENSION');

        $name = $this->template . '.' . $extension;

        $path = $this->getTemplatePath() . $name;

        return [
            'name' => $name,
            'path' => $path
        ];
    }

    /**
     * Alte PHP Template Pfade
     */
    protected function resolvePhpTemplate(): ?string
    {
        $base = $this->getTemplatePath();

        $paths = [

            $base . $this->template . '.html.php',
            $base . $this->template . '.php',
            $base . $this->template . '.html'

        ];

        foreach ($paths as $path)
        {
            if (file_exists($path))
            {
                return $path;
            }
        }

        return null;
    }

    public function renderAsString(): string
    {
        return $this->processTemplate();
    }

    public function render(): void
    {
        echo $this->renderAsString();
    }

    protected function getTemplatePath(): string
    {
        return Asterios::getBasePath() . $this->getEnvData('TEMPLATE_PATH');
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
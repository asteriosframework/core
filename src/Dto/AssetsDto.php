<?php declare(strict_types=1);

/**
 * @codeCoverageIgnore
 */

namespace Asterios\Core\Dto;

use Asterios\Core\Assets;

class AssetsDto
{
    /* @var string */
    protected $path;
    /* @var string */
    protected $file;
    /* @var string|null */
    protected $css_classname = null;
    /* @var string */
    protected $document_type = Assets::DOCTYPE_HTML5;

    /**
     * @param string $path
     * @return AssetsDto
     */
    public function set_path(string $path): AssetsDto
    {
        if (false === strpos($path, DIRECTORY_SEPARATOR, -1))
        {
            $path .= DIRECTORY_SEPARATOR;
        }

        $this->path = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function get_path(): string
    {
        return $this->path;
    }

    /**
     * @param string $file
     * @return AssetsDto
     */
    public function set_file(string $file): AssetsDto
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return string
     */
    public function get_file(): string
    {
        return $this->file;
    }

    /**
     * @param string|null $css_classname
     * @return AssetsDto
     */
    public function set_css_classname(?string $css_classname): AssetsDto
    {
        $this->css_classname = $css_classname;

        return $this;
    }

    /**
     * @return string|null
     */
    public function get_css_classname(): ?string
    {
        return $this->css_classname;
    }

    /**
     * @param string $document_type
     * @return AssetsDto
     */
    public function set_document_type(string $document_type): AssetsDto
    {
        $this->document_type = $document_type;

        return $this;
    }

    /**
     * @return string
     */
    public function get_document_type(): string
    {
        return $this->document_type;
    }
}

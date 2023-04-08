<?php declare(strict_types=1);

namespace Asterios\Core\Traits;

use Asterios\Core\File;

trait FileTrait
{
    /** @var File|null */
    protected $file;

    public function setFile(File $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getFile(): File
    {
        return $this->file ?? File::forge();
    }

}
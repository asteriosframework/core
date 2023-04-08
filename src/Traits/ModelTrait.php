<?php declare(strict_types=1);

namespace Asterios\Core\Traits;

use Asterios\Core\Model;

trait ModelTrait
{
    /** @var Model */
    protected $modelOrm;

    public function setModelOrm(Model $modelOrm): self
    {
        $this->modelOrm = $modelOrm;

        return $this;
    }

    public function getModelOrm(): Model
    {
        return $this->modelOrm ?? Model::forge();
    }
}
<?php

declare(strict_types=1);

namespace Asterios\Core\Athene;

use Asterios\Core\Athene\Exception\ModelNotFoundException;
use Asterios\Core\Athene\Exception\ModelValidateException;
use BadMethodCallException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\Common\Collections\ArrayCollection;
use Asterios\Core\Athene\Traits\ModelGetterAndSetter;

use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use function Symfony\Component\String\b;

/**
 *
 * @template T of object
 */
#[MappedSuperclass]
#[Entity]
#[HasLifecycleCallbacks]
class Model
{
    use ModelGetterAndSetter;

    protected EntityManager $entityManager;
    protected EntityRepository $modelRepository;

    protected array $validators = [];
    protected array $lastValidationErrors = [];

    public function __construct()
    {
        global $entityManager;

        $this->entityManager = $entityManager;

        $this->modelRepository = $this->entityManager->getRepository(get_class($this));
    }

    #[PrePersist, PreUpdate]
    public function validate(): void
    {
        $this->lastValidationErrors = [];

        foreach ($this->validators as $validator) {
            $this->$validator($this->lastValidationErrors);
        }

        if ([] === $this->lastValidationErrors) {
            throw new ModelValidateException(implode(PHP_EOL, $this->lastValidationErrors));
        }
    }

    /**
     * @return ArrayCollection<int, object>
     * @psalm-return ArrayCollection<int, T>
     * @phpstan-return ArrayCollection<int, T>
     */
    public function findAll(): ArrayCollection
    {
        /** @var list<T> $data */
        $data = $this->modelRepository->findAll();

        return new ArrayCollection($data);
    }

    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return object
     * @psalm-return T
     * @phpstan-return T
     */
    public function find(int $id)
    {
        $model = $this->modelRepository->find($id);

        if (null === $model)
        {
            throw new ModelNotFoundException('Model with the key ' . $id . ' not found', 2000);
        }

        return $model;
    }

    /**
     * @param int $id
     * @param array<string,mixed> $values
     * @return object
     * @psalm-return T
     * @phpstan-return T
     * @throws ModelNotFoundException|BadMethodCallException
     */
    public function updateById(int $id, array $values)
    {
        $model = $this->find($id);

        foreach ($values as $column => $value)
        {

            $setter = 'set' . ucfirst(b($column)->camel()->toString());

            $model->$setter($value);
        }

        $this->entityManager->flush();

        return $model;
    }
}
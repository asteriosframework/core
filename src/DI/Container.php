<?php

namespace Asterios\Core\DI;

use Asterios\Core\DI\Exceptions\NotFoundException;
use Asterios\Core\DI\Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;

class Container implements ContainerInterface
{
    /**
     * @var array<string, mixed>
     */
    private $services = [];

    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        $item = $this->resolve($id);
        if (!($item['class'] instanceof ReflectionClass))
        {
            return $item['class'];
        }

        return $this->getInstance($item);
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, $value, $params = []): static
    {
        $this->services[$key] = ['service' => $value, 'params' => $params];
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function has($id): bool
    {
        try
        {
            $item = $this->resolve($id);
        }
        catch (NotFoundException $e)
        {
            return false;
        }
        return $item['class']->isInstantiable();
    }

    /**
     * @param string $id
     * @throws NotFoundException
     * @return array{class: ReflectionClass<object>, params: array<string,mixed>|null}
     */
    private function resolve(string $id): array
    {
        try
        {
            $name = $id;
            if (isset($this->services[$id]))
            {
                $name = $this->services[$id]['service'];
                if (is_callable($name))
                {
                    return ['class' => $name(), 'params' => null];
                }
            }

            return [
                'class' => new ReflectionClass($name),
                'params' => $this->services[$id]['params'] ?? []
            ];
        }
        catch (ReflectionException $e)
        {
            throw new NotFoundException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param array{class: ReflectionClass<object>, params: array<string,mixed>} $item
     * @return mixed
     */
    private function getInstance(array $item)
    {
        $constructor = $item['class']->getConstructor();
        if (is_null($constructor) || ($constructor->getNumberOfRequiredParameters() == 0 && [] === $item['params']))
        {
            return $item['class']->newInstance();
        }

        $params = [];
        foreach ($constructor->getParameters() as $param)
        {
            if (isset($item['params'][$param->getName()]))
            {
                $params[] = $item['params'][$param->getName()];
            }
            else
            {
                if (true === $item['class']->hasProperty($param->getName()))
                {
                    /** @var \ReflectionNamedType $type */
                    $type = $param->getType();
                    if (!$this->isScalarType($type->getName()) && !$param->isOptional())
                    {
                        $params[] = $this->get($type->getName());
                    }
                    elseif ($param->isOptional())
                    {
                        $params[] = $param->getDefaultValue();
                    }
                }
            }
        }

        return $item['class']->newInstanceArgs($params);
    }

    private function isScalarType(string $type): bool
    {
        $scalarTypes = [
            'bool',
            'int',
            'float',
            'string',
            'array'
        ];

        return in_array($type, $scalarTypes);
    }
}

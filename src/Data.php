<?php

declare(strict_types=1);

namespace Asterios\Core;

use ReflectionClass;
use ReflectionProperty;

abstract class Data
{
    /**
     * @var array
     */
    protected static array $requiredFields = [];

    /**
     * @return array
     */
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

            if (!array_key_exists($name, $data) || $data[$name] === null) {
                continue;
            }

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

    /**
     * @param string $docComment
     * @param ReflectionProperty $prop
     * @return string|null
     */
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

    /**
     * @param array $data
     * @param string $key
     * @param callable $setter
     * @return void
     */
    protected function setIfExists(array $data, string $key, callable $setter): void
    {
        if (array_key_exists($key, $data) && $data[$key] !== null) {
            $setter($data[$key]);
        }
    }

    /**
     * @return array
     */
    public function validate(): array
    {
        $errors = [];
        $ref = new ReflectionClass($this);

        foreach ($ref->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $name = $prop->getName();
            $value = $this->{$name};

            if (null === $value && in_array($name, static::$requiredFields, true)) {
                $errors[] = "Property '$name' is required but is null.";
                continue;
            }

            $type = $prop->getType()?->getName();
            if ($type !== null && $value !== null) {
                if (is_subclass_of($type, self::class) && !($value instanceof $type)) {
                    $errors[] = "Property '$name' must be instance of $type.";
                } elseif ($type === 'array' && is_array($value)) {
                    $docComment = $prop->getDocComment() ?: '';
                    $dtoClass = $this->extractDataClassFromVarDoc($docComment, $prop);
                    if ($dtoClass !== null) {
                        foreach ($value as $i => $item) {
                            if (!($item instanceof $dtoClass)) {
                                $errors[] = "Item $i in '$name' must be instance of $dtoClass.";
                            }
                        }
                    }
                }
            }
        }

        return $errors;
    }
}

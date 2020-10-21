<?php

/**
 * @see       https://github.com/open-code-modeling/json-schema-to-php for the canonical source repository
 * @copyright https://github.com/open-code-modeling/json-schema-to-php/blob/master/COPYRIGHT.md
 * @license   https://github.com/open-code-modeling/json-schema-to-php/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace OpenCodeModeling\JsonSchemaToPhp\Type;

use Countable;
use Iterator;

/**
 * @implements \Iterator<TypeDefinition>
 */
final class TypeSet implements Iterator, Countable, RequiredAware, NullableAware
{
    /**
     * @var TypeDefinition[]
     **/
    private array $types;

    private int $position = 0;

    public function __construct(TypeDefinition ...$types)
    {
        $this->types = $types;
    }

    public function first(): ?TypeDefinition
    {
        return $this->types[0] ?? null;
    }

    public function last(): ?TypeDefinition
    {
        if (\count($this->types) === 0) {
            return null;
        }

        return $this->types[\count($this->types) - 1];
    }

    public function filter(callable $filter): self
    {
        return new self(...\array_filter($this->types, static fn ($item) => $filter($item)));
    }

    /**
     * @return TypeDefinition[]
     */
    public function types(): array
    {
        return $this->types;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function current(): TypeDefinition
    {
        return $this->types[$this->position];
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function valid(): bool
    {
        return isset($this->types[$this->position]);
    }

    public function count(): int
    {
        return \count($this->types);
    }

    public function setIsRequired(bool $required): void
    {
        foreach ($this->types as $type) {
            if ($type instanceof RequiredAware) {
                $type->setIsRequired($required);
            }
        }
    }

    public function setNullable(bool $nullable): void
    {
        foreach ($this->types as $type) {
            if ($type instanceof NullableAware) {
                $type->setNullable($nullable);
            }
        }
    }

    public function __clone()
    {
        $types = [];

        foreach ($this->types as $type) {
            $types[] = clone $type;
        }
        $this->types = $types;
    }
}

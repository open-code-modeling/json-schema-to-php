<?php

/**
 * @see       https://github.com/open-code-modeling/json-schema-to-php for the canonical source repository
 * @copyright https://github.com/open-code-modeling/json-schema-to-php/blob/master/COPYRIGHT.md
 * @license   https://github.com/open-code-modeling/json-schema-to-php/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace OpenCodeModeling\JsonSchemaToPhp\Type;

class ConstType implements TypeDefinition
{
    private ?string $name = null;

    /**
     * @var mixed
     */
    private $value;

    final private function __construct()
    {
    }

    public function isNullable(): bool
    {
        return false;
    }

    public function isRequired(): bool
    {
        return false;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public static function fromDefinition(array $definition, ?string $name = null): self
    {
        $self = new static();
        $self->name = $name;
        $self->value = $definition['const'];

        return $self;
    }

    /**
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }

    public static function type(): string
    {
        return 'const';
    }
}

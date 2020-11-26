<?php

/**
 * @see       https://github.com/open-code-modeling/json-schema-to-php for the canonical source repository
 * @copyright https://github.com/open-code-modeling/json-schema-to-php/blob/master/COPYRIGHT.md
 * @license   https://github.com/open-code-modeling/json-schema-to-php/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace OpenCodeModeling\JsonSchemaToPhp\Type;

final class NotType implements TypeDefinition
{
    private ?string $name = null;

    private TypeSet $typeSet;

    private function __construct()
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

    /**
     * @param array<string, mixed> $definition
     * @param string|null $name
     * @param array<string, TypeSet> $rootDefinitions
     * @return static
     */
    public static function fromDefinition(array $definition, ?string $name = null, array $rootDefinitions = []): self
    {
        $self = new static();
        $self->name = $name;
        $self->typeSet = Type::fromDefinition($definition['not'], null, $rootDefinitions);

        return $self;
    }

    public function getTypeSet(): TypeSet
    {
        return $this->typeSet;
    }

    public static function type(): string
    {
        return 'not';
    }
}

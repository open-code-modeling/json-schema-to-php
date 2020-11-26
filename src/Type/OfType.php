<?php

/**
 * @see       https://github.com/open-code-modeling/json-schema-to-php for the canonical source repository
 * @copyright https://github.com/open-code-modeling/json-schema-to-php/blob/master/COPYRIGHT.md
 * @license   https://github.com/open-code-modeling/json-schema-to-php/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace OpenCodeModeling\JsonSchemaToPhp\Type;

/**
 * @internal
 */
abstract class OfType implements TypeDefinition
{
    private ?string $name = null;

    /**
     * @var TypeSet[]
     */
    private array $typeSets = [];

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

        $type = $definition['type'] ?: static::type();

        foreach ($definition[$type] as $typeDefinition) {
            $self->typeSets[] = Type::fromDefinition($typeDefinition, null, $rootDefinitions);
        }

        return $self;
    }

    /**
     * @return TypeSet[]
     */
    public function getTypeSets(): array
    {
        return $this->typeSets;
    }
}

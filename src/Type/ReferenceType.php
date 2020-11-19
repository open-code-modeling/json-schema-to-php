<?php

/**
 * @see       https://github.com/open-code-modeling/json-schema-to-php for the canonical source repository
 * @copyright https://github.com/open-code-modeling/json-schema-to-php/blob/master/COPYRIGHT.md
 * @license   https://github.com/open-code-modeling/json-schema-to-php/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace OpenCodeModeling\JsonSchemaToPhp\Type;

final class ReferenceType implements TypeDefinition, RequiredAware, NullableAware, TitleAware
{
    protected ?TypeSet $resolvedType = null;
    protected ?string $name = null;
    protected ?string $ref = null;
    protected bool $isRequired = false;
    protected bool $nullable = false;
    protected ?string $title = null;

    private function __construct()
    {
    }

    public static function fromDefinition(array $definition, ?string $name = null): self
    {
        if (! isset($definition['$ref'])) {
            throw new \RuntimeException(\sprintf('The "$ref" is missing in schema definition for "%s"', $name));
        }

        $referencePath = \explode('/', $definition['$ref']);
        $name = \array_pop($referencePath);

        $self = new static();
        $self->setName($name);

        foreach ($definition as $definitionKey => $definitionValue) {
            if (\property_exists($self, $definitionKey)) {
                $self->$definitionKey = $definitionValue;
            }
        }

        return $self;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function ref(): ?string
    {
        return $this->ref;
    }

    public function setRef(string $ref): self
    {
        $this->ref = $ref;

        return $this;
    }

    public static function type(): string
    {
        return self::TYPE_REF;
    }

    public function setResolvedType(TypeSet $resolvedType): void
    {
        $this->resolvedType = $resolvedType;
    }

    public function resolvedType(): ?TypeSet
    {
        return $this->resolvedType;
    }

    public function setIsRequired(bool $required): void
    {
        if ($this->resolvedType !== null) {
            foreach ($this->resolvedType as $type) {
                if ($type instanceof RequiredAware) {
                    $type->setIsRequired($required);
                }
            }
        }
        $this->isRequired = $required;
    }

    public function setNullable(bool $nullable): void
    {
        if ($this->resolvedType !== null) {
            foreach ($this->resolvedType as $type) {
                if ($type instanceof NullableAware) {
                    $type->setNullable($nullable);
                }
            }
        }
        $this->nullable = $nullable;
    }

    public function title(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}

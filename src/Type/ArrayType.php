<?php

/**
 * @see       https://github.com/open-code-modeling/json-schema-to-php for the canonical source repository
 * @copyright https://github.com/open-code-modeling/json-schema-to-php/blob/master/COPYRIGHT.md
 * @license   https://github.com/open-code-modeling/json-schema-to-php/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace OpenCodeModeling\JsonSchemaToPhp\Type;

final class ArrayType implements TypeDefinition
{
    use PopulateRequired;

    /**
     * @var TypeSet[]
     */
    protected array $items = [];

    /**
     * @var TypeSet[]
     */
    protected array $contains = [];

    /**
     * @var array<string, TypeSet>
     */
    protected array $definitions = [];

    protected ?TypeSet $additionalItems = null;
    protected ?bool $uniqueItems = null;
    protected ?int $maxItems = null;
    protected ?int $minItems = null;
    protected ?string $name = null;
    protected bool $isRequired = false;
    protected bool $nullable = false;

    private function __construct()
    {
    }

    public static function fromDefinition(array $definition, ?string $name = null): self
    {
        if (! isset($definition['type']) && ! isset($definition['$ref'])) {
            throw new \RuntimeException(\sprintf('The "type" is missing in schema definition for "%s"', $name));
        }

        $type = $definition['type'] ?? '$ref';

        if ($type !== static::type()) {
            throw new \RuntimeException(
                \sprintf('The type "%s" does not match type "%s" class "%s"', $type, self::type(), static::class)
            );
        }

        $self = new static();
        $self->setName($name);

        if (isset($definition['definitions'])) {
            foreach ($definition['definitions'] as $propertyName => $propertyDefinition) {
                $self->definitions[$propertyName] = Type::fromDefinition($propertyDefinition, $propertyName);
            }
        }

        // definitions can be shared and must be cloned to not override defaults e. g. required
        $resolveReference = static function (string $ref) use ($self) {
            $referencePath = \explode('/', $ref);
            $name = \array_pop($referencePath);

            $resolvedType = $self->definitions[$name] ?? null;

            return $resolvedType ? clone $resolvedType : null;
        };

        $populateArrayType = static function (string $key, array $definitionValue) use ($resolveReference, $self) {
            if (isset($definitionValue['type'])) {
                $self->$key[] = Type::fromDefinition($definitionValue, '');

                return;
            }
            foreach ($definitionValue as $propertyDefinition) {
                if (isset($propertyDefinition['type'])) {
                    $self->$key[] = Type::fromDefinition($propertyDefinition, '');
                } elseif (isset($propertyDefinition['$ref'])) {
                    $ref = ReferenceType::fromDefinition($propertyDefinition, '');
                    $ref->setResolvedType($resolveReference($propertyDefinition['$ref']));
                    $self->$key[] = new TypeSet($ref);
                }
            }
        };

        foreach ($definition as $definitionKey => $definitionValue) {
            switch ($definitionKey) {
                case 'items':
                    $populateArrayType('items', $definitionValue);
                    break;
                case 'contains':
                    $populateArrayType('contains', $definitionValue);
                    break;
                case 'additionalItems':
                    $self->additionalItems = Type::fromDefinition($definitionValue, '');
                    break;
                case 'definitions':
                    // handled beforehand
                    break;
                default:
                    if (\property_exists($self, $definitionKey)) {
                        $self->$definitionKey = $definitionValue;
                    }
                    break;
            }
        }

        $self->populateRequired('items');
        $self->populateRequired('contains');
        $self->populateRequired('additionalItems');

        return $self;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function setNullable(bool $nullable): self
    {
        $this->nullable = $nullable;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function setIsRequired(bool $required): void
    {
        $this->isRequired = $required;
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
     * @return array<string, TypeSet>
     */
    public function definitions(): array
    {
        return $this->definitions;
    }

    /**
     * @return TypeSet[]
     */
    public function items(): array
    {
        return $this->items;
    }

    public function setItems(TypeSet ...$items): self
    {
        $this->items = $items;

        return $this;
    }

    public function addItem(TypeSet $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    public function minItems(): ?int
    {
        return $this->minItems;
    }

    public function setMinItems(int $minItems): self
    {
        $this->minItems = $minItems;

        return $this;
    }

    public function maxItems(): ?int
    {
        return $this->maxItems;
    }

    public function setMaxItems(int $maxItems): self
    {
        $this->maxItems = $maxItems;

        return $this;
    }

    public function isUniqueItems(): ?bool
    {
        return $this->uniqueItems;
    }

    public function setUniqueItems(bool $uniqueItems): self
    {
        $this->uniqueItems = $uniqueItems;

        return $this;
    }

    /**
     * @return TypeSet[]
     */
    public function contains(): array
    {
        return $this->contains;
    }

    public function additionalItems(): ?TypeSet
    {
        return $this->additionalItems;
    }

    public static function type(): string
    {
        return self::TYPE_ARRAY;
    }
}

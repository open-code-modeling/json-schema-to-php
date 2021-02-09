<?php

/**
 * @see       https://github.com/open-code-modeling/json-schema-to-php for the canonical source repository
 * @copyright https://github.com/open-code-modeling/json-schema-to-php/blob/master/COPYRIGHT.md
 * @license   https://github.com/open-code-modeling/json-schema-to-php/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace OpenCodeModeling\JsonSchemaToPhp\Type;

final class ArrayType implements TypeDefinition, TitleAware, CustomSupport
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
    protected ?string $title = null;

    /**
     * @var array<string, mixed>
     */
    protected array $custom = [];

    private function __construct()
    {
    }

    /**
     * @param array<string, mixed> $definition
     * @param string|null $name
     * @param array<string, TypeSet> $rootDefinitions
     * @return static
     */
    public static function fromDefinition(array $definition, ?string $name = null, array $rootDefinitions = []): self
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
                $self->definitions[$propertyName] = Type::fromDefinition($propertyDefinition, $propertyName, $rootDefinitions);
            }
        }

        // definitions can be shared and must be cloned to not override defaults e. g. required
        $resolveReference = static function (string $ref) use ($self, $rootDefinitions) {
            $referencePath = \explode('/', $ref);
            $name = \array_pop($referencePath);

            $resolvedType = $self->definitions[$name] ?? null;

            if ($resolvedType === null) {
                $resolvedType = $rootDefinitions[$name] ?? null;
            }

            return $resolvedType ? clone $resolvedType : null;
        };

        $populateArrayType = static function (string $key, array $definitionValue) use ($resolveReference, $self) {
            switch (true) {
                case isset($definitionValue['type']):
                    $self->$key[] = Type::fromDefinition($definitionValue, '', $self->definitions());
                    break;
                case isset($definitionValue['$ref']):
                    $ref = ReferenceType::fromDefinition($definitionValue, '');

                    if ($resolvedType = $resolveReference($definitionValue['$ref'])) {
                        $ref->setResolvedType($resolveReference($definitionValue['$ref']));
                    }
                    $self->$key[] = new TypeSet($ref);
                    break;
                default:
                    foreach ($definitionValue as $propertyDefinition) {
                        if (isset($propertyDefinition['$ref'])) {
                            $ref = ReferenceType::fromDefinition($propertyDefinition, '');

                            if ($resolvedType = $resolveReference($propertyDefinition['$ref'])) {
                                $ref->setResolvedType($resolveReference($propertyDefinition['$ref']));
                            }

                            $self->$key[] = new TypeSet($ref);
                        } else {
                            $self->$key[] = Type::fromDefinition(
                                isset($propertyDefinition[0])
                                    ? $definitionValue
                                    : $propertyDefinition,
                                '',
                                $self->definitions()
                            );
                        }
                    }
                    break;
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
                    $self->additionalItems = Type::fromDefinition($definitionValue, '', $self->definitions());
                    break;
                case 'definitions':
                case 'type':
                    // handled beforehand
                    break;
                default:
                    if (\property_exists($self, $definitionKey)) {
                        $self->$definitionKey = $definitionValue;
                    } else {
                        $self->custom[$definitionKey] = $definitionValue;
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

    public function title(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public static function type(): string
    {
        return self::TYPE_ARRAY;
    }

    /**
     * Returns custom definitions
     *
     * @return array<string, mixed>
     */
    public function custom(): array
    {
        return $this->custom;
    }
}

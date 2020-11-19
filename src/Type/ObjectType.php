<?php

/**
 * @see       https://github.com/open-code-modeling/json-schema-to-php for the canonical source repository
 * @copyright https://github.com/open-code-modeling/json-schema-to-php/blob/master/COPYRIGHT.md
 * @license   https://github.com/open-code-modeling/json-schema-to-php/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace OpenCodeModeling\JsonSchemaToPhp\Type;

final class ObjectType implements TypeDefinition, NullableAware, RequiredAware, TitleAware
{
    use PopulateRequired;

    protected ?string $name = null;
    protected bool $isRequired = false;
    protected bool $nullable = false;
    protected ?string $title = null;

    /**
     * @var null|bool|TypeSet
     */
    protected $additionalProperties;

    /**
     * @var array<string, string[]>
     */
    protected array $dependencies = [];

    /**
     * @var array<string, TypeSet>
     */
    protected array $properties = [];

    /**
     * @var array<string>
     */
    protected array $required = [];

    /**
     * @var array<string, TypeSet>
     */
    protected array $definitions = [];

    private function __construct()
    {
    }

    public static function fromDefinition(array $definition, ?string $name = null): self
    {
        if (! isset($definition['type'])) {
            throw new \RuntimeException(\sprintf('The "type" is missing in schema definition for "%s"', $name));
        }

        $type = $definition['type'];

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

        foreach ($definition as $definitionKey => $definitionValue) {
            switch ($definitionKey) {
                case 'properties':
                    foreach ($definitionValue as $propertyName => $propertyDefinition) {
                        if (isset($propertyDefinition['$ref'])) {
                            $ref = ReferenceType::fromDefinition($propertyDefinition, '');

                            if ($resolvedType = $resolveReference($propertyDefinition['$ref'])) {
                                $ref->setResolvedType($resolvedType);
                            }
                            $self->properties[$propertyName] = new TypeSet($ref);
                        } else {
                            $self->properties[$propertyName] = Type::fromDefinition(
                                $propertyDefinition,
                                $propertyName
                            );
                        }
                    }
                    break;
                case 'additionalProperties':
                    $self->additionalProperties = \is_array($definitionValue)
                        ? Type::fromDefinition($definitionValue, '')
                        : $definitionValue;
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

        $self->populateRequired('properties');
        $self->populateRequired('additionalProperties');

        return $self;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function setNullable(bool $nullable): void
    {
        $this->nullable = $nullable;
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

    public static function type(): string
    {
        return self::TYPE_OBJECT;
    }

    /**
     * @return null|bool|TypeSet
     */
    public function additionalProperties()
    {
        return $this->additionalProperties;
    }

    /**
     * @return array<string, string[]>
     */
    public function dependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * @return array<string, TypeSet>
     */
    public function properties(): array
    {
        return $this->properties;
    }

    /**
     * @return array<string>
     */
    public function required(): array
    {
        return $this->required;
    }

    public function title(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return array<string, TypeSet>
     */
    public function definitions(): array
    {
        return $this->definitions;
    }
}

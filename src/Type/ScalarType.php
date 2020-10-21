<?php

/**
 * @see       https://github.com/open-code-modeling/json-schema-to-php for the canonical source repository
 * @copyright https://github.com/open-code-modeling/json-schema-to-php/blob/master/COPYRIGHT.md
 * @license   https://github.com/open-code-modeling/json-schema-to-php/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace OpenCodeModeling\JsonSchemaToPhp\Type;

abstract class ScalarType implements TypeDefinition, RequiredAware, NullableAware
{
    protected ?string $format = null;
    protected ?string $name = null;
    protected bool $isRequired = false;
    protected bool $nullable = false;

    /**
     * @var mixed
     */
    protected $const;

    /**
     * @var mixed
     */
    protected $default;

    /**
     * @var ?array<string>
     */
    protected ?array $enum;

    final private function __construct()
    {
    }

    public static function fromDefinition(array $definition, ?string $name = null): self
    {
        if (! isset($definition['type'])) {
            throw new \RuntimeException(\sprintf('The "type" is missing in schema definition for "%s"', $name));
        }

        $type = $definition['type'];

        if ($type !== static::type()) {
            throw new \RuntimeException(\sprintf('The type "%s" does not match type class "%s"', $type, static::class));
        }

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

    public function setNullable(bool $nullable): void
    {
        $this->nullable = $nullable;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function format(): ?string
    {
        return $this->format;
    }

    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    /**
     * @return array<string>|null
     */
    public function enum(): ?array
    {
        return $this->enum;
    }

    public function setEnum(string ...$enum): void
    {
        $this->enum = $enum;
    }

    /**
     * @return mixed
     */
    public function const()
    {
        return $this->const;
    }

    /**
     * @param mixed $const
     */
    public function setConst($const): void
    {
        $this->const = $const;
    }

    /**
     * @return mixed
     */
    public function default()
    {
        return $this->default;
    }

    /**
     * @param mixed $default
     */
    public function setDefault($default): void
    {
        $this->default = $default;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function setIsRequired(bool $required): void
    {
        $this->isRequired = $required;
    }
}

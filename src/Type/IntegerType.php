<?php

/**
 * @see       https://github.com/open-code-modeling/json-schema-to-php for the canonical source repository
 * @copyright https://github.com/open-code-modeling/json-schema-to-php/blob/master/COPYRIGHT.md
 * @license   https://github.com/open-code-modeling/json-schema-to-php/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace OpenCodeModeling\JsonSchemaToPhp\Type;

final class IntegerType extends ScalarType
{
    protected ?bool $exclusiveMaximum = null;
    protected ?bool $exclusiveMinimum = null;
    protected int $maximum;
    protected int $minimum;
    protected int $multipleOf;

    public function minimum(): int
    {
        return $this->minimum;
    }

    public function setMinimum(int $minimum): self
    {
        $this->minimum = $minimum;

        return $this;
    }

    public function maximum(): int
    {
        return $this->maximum;
    }

    public function setMaximum(int $maximum): self
    {
        $this->maximum = $maximum;

        return $this;
    }

    public function exclusiveMinimum(): ?bool
    {
        return $this->exclusiveMinimum;
    }

    public function setExclusiveMinimum(bool $exclusiveMinimum): self
    {
        $this->exclusiveMinimum = $exclusiveMinimum;

        return $this;
    }

    public function exclusiveMaximum(): ?bool
    {
        return $this->exclusiveMaximum;
    }

    public function setExclusiveMaximum(bool $exclusiveMaximum): self
    {
        $this->exclusiveMaximum = $exclusiveMaximum;

        return $this;
    }

    public function multipleOf(): int
    {
        return $this->multipleOf;
    }

    public function setMultipleOf(int $multipleOf): self
    {
        $this->multipleOf = $multipleOf;

        return $this;
    }

    public static function type(): string
    {
        return self::TYPE_INTEGER;
    }
}

<?php

/**
 * @see       https://github.com/open-code-modeling/json-schema-to-php for the canonical source repository
 * @copyright https://github.com/open-code-modeling/json-schema-to-php/blob/master/COPYRIGHT.md
 * @license   https://github.com/open-code-modeling/json-schema-to-php/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace OpenCodeModeling\JsonSchemaToPhp\Type;

final class NumberType extends ScalarType
{
    protected ?bool $exclusiveMaximum = null;
    protected ?bool $exclusiveMinimum = null;
    protected float $maximum;
    protected float $minimum;
    protected float $multipleOf;

    public function minimum(): float
    {
        return $this->minimum;
    }

    public function setMinimum(float $minimum): self
    {
        $this->minimum = $minimum;

        return $this;
    }

    public function maximum(): float
    {
        return $this->maximum;
    }

    public function setMaximum(float $maximum): self
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
        $copy = clone $this;
        $copy->exclusiveMinimum = $exclusiveMinimum;

        return $copy;
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

    public function multipleOf(): float
    {
        return $this->multipleOf;
    }

    public function setMultipleOf(float $multipleOf): self
    {
        $this->multipleOf = $multipleOf;

        return $this;
    }

    public static function type(): string
    {
        return self::TYPE_NUMBER;
    }
}

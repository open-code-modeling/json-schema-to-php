<?php

/**
 * @see       https://github.com/open-code-modeling/json-schema-to-php for the canonical source repository
 * @copyright https://github.com/open-code-modeling/json-schema-to-php/blob/master/COPYRIGHT.md
 * @license   https://github.com/open-code-modeling/json-schema-to-php/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace OpenCodeModeling\JsonSchemaToPhp\Exception;

final class InvalidShorthand extends LogicException
{
    /**
     * @var array<string, mixed>
     */
    private array $shorthand;

    /**
     * @param string $schemaProperty
     * @param array<string, mixed> $shorthand
     * @return static
     */
    public static function cannotParseProperty(string $schemaProperty, array $shorthand): self
    {
        $self = new static(
            \sprintf(
                'I tried to parse JSONSchema for property: "%s", but it is neither a string nor an object.',
                $schemaProperty
            )
        );
        $self->shorthand = $shorthand;

        return $self;
    }

    /**
     * @param array<string, mixed> $shorthand $shorthand
     * @return static
     */
    public static function emptyString(array $shorthand): self
    {
        $self = new static('Shorthand contains an empty or non string property. Cannot deal with that!');
        $self->shorthand = $shorthand;

        return $self;
    }

    /**
     * @param array<string, mixed> $shorthand $shorthand
     * @return static
     */
    public static function refNotString(array $shorthand): self
    {
        $self = new static(
            'Detected a top level shorthand reference using a "$ref" property, but the value of the property is not a string.'
        );
        $self->shorthand = $shorthand;

        return $self;
    }

    /**
     * @param array<string, mixed> $shorthand $shorthand
     * @return static
     */
    public static function itemsNotString(array $shorthand): self
    {
        $self = new static(
            'Detected a top level shorthand array using an "$items" property, but the value of the property is not a string.'
        );
        $self->shorthand = $shorthand;

        return $self;
    }

    /**
     * @param array<string, mixed> $shorthand $shorthand
     * @return static
     */
    public static function refWithOtherProperties(array $shorthand): self
    {
        $self = new static(
            'Shorthand contains a top level ref property "$ref", but it is not the only property!'
            . ' A top level reference cannot have other properties then "$ref".'
        );
        $self->shorthand = $shorthand;

        return $self;
    }

    /**
     * @param array<string, mixed> $shorthand $shorthand
     * @return static
     */
    public static function itemsWithOtherProperties(array $shorthand): self
    {
        $self = new static(
            'Shorthand %s contains a top level array property "$items", but it is not the only property!'
            . ' A top level array cannot have other properties then "$items".'
        );
        $self->shorthand = $shorthand;

        return $self;
    }

    /**
     * @return array<string, mixed>
     */
    public function shorthand(): array
    {
        return $this->shorthand;
    }
}

<?php

/**
 * @see       https://github.com/open-code-modeling/json-schema-to-php for the canonical source repository
 * @copyright https://github.com/open-code-modeling/json-schema-to-php/blob/master/COPYRIGHT.md
 * @license   https://github.com/open-code-modeling/json-schema-to-php/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace OpenCodeModeling\JsonSchemaToPhp\Type;

interface TypeDefinition
{
    public const TYPE_REF = '$ref';
    public const TYPE_ANY = 'any';
    public const TYPE_ARRAY = 'array';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_NUMBER = 'number';
    public const TYPE_OBJECT = 'object';
    public const TYPE_STRING = 'string';

    public const FORMAT_BINARY = 'base64';
    public const FORMAT_DATE = 'date';
    public const FORMAT_DATETIME = 'date-time';
    public const FORMAT_DURATION = 'duration';
    public const FORMAT_INT32 = 'int32';
    public const FORMAT_INT64 = 'int64';
    public const FORMAT_TIME = 'time';
    public const FORMAT_URI = 'uri';

    public function isNullable(): bool;

    public function isRequired(): bool;

    public function name(): ?string;

    public function setName(?string $name): void;

    public static function type(): string;

    /**
     * @param array<string, mixed> $definition
     * @param string|null $name
     * @return TypeDefinition
     */
    public static function fromDefinition(array $definition, ?string $name = null): TypeDefinition;
}

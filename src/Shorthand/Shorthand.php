<?php

/**
 * @see       https://github.com/open-code-modeling/json-schema-to-php for the canonical source repository
 * @copyright https://github.com/open-code-modeling/json-schema-to-php/blob/master/COPYRIGHT.md
 * @license   https://github.com/open-code-modeling/json-schema-to-php/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace OpenCodeModeling\JsonSchemaToPhp\Shorthand;

use OpenCodeModeling\JsonSchemaToPhp\Exception\InvalidShorthand;
use OpenCodeModeling\JsonSchemaToPhp\Exception\LogicException;

final class Shorthand
{
    /**
     * @param array<string, mixed> $shorthand
     * @param string|null $namespace
     * @return array<string, mixed>
     */
    public static function convertToJsonSchema(array $shorthand, ?string $namespace = null): array
    {
        $schema = [
            'type' => 'object',
            'properties' => [

            ],
            'required' => [],
            'additionalProperties' => false,
        ];

        if ($namespace !== null) {
            $schema['namespace'] = $namespace;
        }

        foreach ($shorthand as $property => $shorthandDefinition) {
            if (! \is_string($property) || empty($property)) {
                throw InvalidShorthand::emptyString($shorthand);
            }
            $schemaProperty = $property;

            switch (true) {
                case \mb_substr($property, -1) === '?':
                    $schemaProperty = \mb_substr($property, 0, -1);
                    break;
                case $schemaProperty === '$ref':
                    if (\count($shorthand) > 1) {
                        throw InvalidShorthand::refWithOtherProperties($shorthand);
                    }

                    if (! \is_string($shorthandDefinition)) {
                        throw InvalidShorthand::refNotString($shorthand);
                    }

                    $shorthandDefinition = \str_replace('#/definitions/', '', $shorthandDefinition);

                    return [
                        '$ref' => "#/definitions/$shorthandDefinition",
                    ];
                case $schemaProperty === '$items':
                    if (\count($shorthand) > 1) {
                        throw InvalidShorthand::itemsWithOtherProperties($shorthand);
                    }

                    if (! \is_string($shorthandDefinition)) {
                        throw InvalidShorthand::itemsNotString($shorthand);
                    }

                    if (\mb_substr($shorthandDefinition, -2) !== '[]') {
                        $shorthandDefinition .= '[]';
                    }

                    return self::convertShorthandStringToJsonSchema($shorthandDefinition);
                case $schemaProperty === '$title':
                    $schema['title'] = $shorthandDefinition;
                    continue 2;
                default:
                    $schema['required'][] = $schemaProperty;
                    break;
            }

            if (\is_array($shorthandDefinition)) {
                $schema['properties'][$schemaProperty] = self::convertToJsonSchema($shorthandDefinition);
            } elseif (\is_string($shorthandDefinition)) {
                $schema['properties'][$schemaProperty] = self::convertShorthandStringToJsonSchema($shorthandDefinition);
            } else {
                throw InvalidShorthand::cannotParseProperty($schemaProperty, $shorthand);
            }
        }

        return $schema;
    }

    /**
     * @param string $shorthandStr
     * @return array<string, mixed>
     */
    private static function convertShorthandStringToJsonSchema(string $shorthandStr): array
    {
        if ($shorthandStr === '') {
            return ['type' => 'string'];
        }

        $parts = \explode('|', $shorthandStr);

        if (\mb_substr($parts[0], -2) === '[]') {
            $itemsParts = [\mb_substr($parts[0], 0, -2)];
            \array_push($itemsParts, ...\array_slice($parts, 1));

            return [
                'type' => 'array',
                'items' => self::convertShorthandStringToJsonSchema(\implode('|', $itemsParts)),
            ];
        }

        switch (true) {
            case \mb_strpos($parts[0], 'string') === 0:
            case \mb_strpos($parts[0], 'integer') === 0:
            case \mb_strpos($parts[0], 'number') === 0:
            case \mb_strpos($parts[0], 'boolean') === 0:
            case \mb_strpos($parts[0], 'enum:') === 0:
                $type = $parts[0];
                $typeKey = 'type';
                $typeValue = $type;

                if (\mb_strpos($parts[0], 'enum:') === 0) {
                    $typeValue = \explode(',', \mb_substr($parts[0], 5));
                    $typeKey = 'enum';
                }

                if (isset($parts[1]) && $parts[1] === 'null') {
                    $typeValue = [$type, 'null'];

                    \array_splice($parts, 1, 1);
                }

                $schema = self::populateSchema($parts);
                $schema[$typeKey] = $typeValue;

                return $schema;
            default:
                $type = $parts[0];

                $schema = self::populateSchema($parts);

                $schema['$ref'] = '#/definitions/'.$type;

                return $schema;
        }
    }

    /**
     * @param array<int, mixed> $parts
     * @return array<string, mixed>
     */
    private static function populateSchema(array $parts): array
    {
        $schema = [];

        if (\count($parts) > 1) {
            $parts = \array_slice($parts, 1);

            foreach ($parts as $part) {
                [$validationKey, $validationValue] = self::parseShorthandValidation($part);

                $schema[$validationKey] = $validationValue;
            }
        }

        return $schema;
    }

    /**
     * @param string $shorthandValidation
     * @return array<mixed>
     */
    private static function parseShorthandValidation(string $shorthandValidation): array
    {
        $parts = \explode(':', $shorthandValidation);

        if (\count($parts) !== 2) {
            throw new LogicException(\sprintf(
                'Cannot parse shorthand validation: "%s". Expected format "validationKey:value". Please check again!',
                $shorthandValidation
            ));
        }

        [$validationKey, $value] = $parts;

        if ($value === 'true') {
            return [$validationKey, true];
        }

        if ($value === 'false') {
            return [$validationKey, false];
        }

        if ((string) (int) $value === $value) {
            return [$validationKey, (int) $value];
        }

        if ((string) (float) $value === $value) {
            return [$validationKey, (float) $value];
        }

        return [$validationKey, $value];
    }
}

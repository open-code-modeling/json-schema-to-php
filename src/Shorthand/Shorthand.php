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
    private const STRING_NAME = '__this_is_my_temporary_property__';

    /**
     * @param string|array<string, mixed> $shorthand
     * @param array<string, mixed> $customData
     * @return array<string, mixed>
     */
    public static function convertToJsonSchema($shorthand, array $customData = []): array
    {
        $isString = \is_string($shorthand);

        if ($isString === true) {
            $shorthand = [self::STRING_NAME => $shorthand];
        }

        $schema = \array_merge(
            $customData,
            [
                'type' => 'object',
                'properties' => [

                ],
                'required' => [],
                'additionalProperties' => false,
            ]
        );

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

                    return self::convertShorthandStringToJsonSchema($shorthandDefinition, $customData);
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
                $schema['properties'][$schemaProperty] = self::convertShorthandStringToJsonSchema($shorthandDefinition, $customData);
            } else {
                throw InvalidShorthand::cannotParseProperty($schemaProperty, $shorthand);
            }
        }

        if ($isString === true) {
            return \array_merge($customData, $schema['properties'][self::STRING_NAME]);
        }

        return $schema;
    }

    /**
     * @param string $shorthandStr
     * @param array<string, mixed> $customData
     * @return array<string, mixed>
     */
    private static function convertShorthandStringToJsonSchema(string $shorthandStr, array $customData): array
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
                'items' => self::convertShorthandStringToJsonSchema(\implode('|', $itemsParts), $customData),
            ];
        }

        $type = $parts[0];
        $namespace = $customData['voNamespace'] ?? '';
        $namespaceDetected = false !== \strpos($parts[0], '/');

        if ($namespace !== '') {
            $namespace = \rtrim($namespace, '/') . '/';
        }

        if ($namespaceDetected) {
            $namespace = self::extractNamespace($type);
            $type = self::extractType($type);
        }

        switch (true) {
            case \mb_strpos($type, 'string') === 0:
            case \mb_strpos($type, 'integer') === 0:
            case \mb_strpos($type, 'number') === 0:
            case \mb_strpos($type, 'boolean') === 0:
            case \mb_strpos($type, 'enum:') === 0:
                $typeKey = 'type';
                $typeValue = $type;

                if (\mb_strpos($type, 'enum:') === 0) {
                    $typeValue = \explode(',', \mb_substr($type, 5));
                    $typeKey = 'enum';
                }

                if (isset($parts[1]) && $parts[1] === 'null') {
                    $typeValue = [$type, 'null'];

                    \array_splice($parts, 1, 1);
                }

                $schema = self::populateSchema($parts);
                $schema[$typeKey] = $typeValue;

                if ($namespaceDetected) {
                    $schema['namespace'] = \strlen($namespace) > 1 ? \rtrim($namespace, '/') : $namespace;
                }

                return $schema;
            default:
                $schema = self::populateSchema($parts);

                $schema['$ref'] = '#/definitions/' . \ltrim($namespace, '/') . $type;

                if ($namespaceDetected) {
                    $schema['namespace'] = \strlen($namespace) > 1 ? \rtrim($namespace, '/') : $namespace;
                }

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

    private static function extractType(string $type): string
    {
        return \trim(\substr($type, \strrpos($type, '/') + 1), '/');
    }

    private static function extractNamespace(string $type): string
    {
        return \substr($type, 0, \strrpos($type, '/') + 1);
    }
}

<?php

/**
 * @see       https://github.com/open-code-modeling/json-schema-to-php for the canonical source repository
 * @copyright https://github.com/open-code-modeling/json-schema-to-php/blob/master/COPYRIGHT.md
 * @license   https://github.com/open-code-modeling/json-schema-to-php/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace OpenCodeModeling\JsonSchemaToPhp\Shorthand;

use LogicException;

final class Shorthand
{
    /**
     * @param array<string, mixed> $shorthand
     * @return array<string, mixed>
     */
    public static function convertToJsonSchema(array $shorthand): array
    {
        $schema = [
            'type' => 'object',
            'properties' => [

            ],
            'required' => [],
            'additionalProperties' => false,
        ];

        foreach ($shorthand as $property => $shorthandDefinition) {
            if (! \is_string($property) || empty($property)) {
                throw new LogicException(\sprintf(
                    'Shorthand %s contains an empty or non string property. Cannot deal with that!',
                    \json_encode($shorthand)
                ));
            }

            $schemaProperty = $property;

            if (\mb_substr($property, -1) === '?') {
                $schemaProperty = \mb_substr($property, 0, \strlen($property) - 1);
            } elseif ($schemaProperty === '$ref') {
                if (\count($shorthand) > 1) {
                    throw new LogicException(\sprintf(
                       'Shorthand %s contains a top level ref property "$ref", but it is not the only property!
                       \nA top level reference cannot have other properties then "$ref".',
                       \json_encode($shorthand)
                   ));
                }

                if (! \is_string($shorthandDefinition)) {
                    throw new LogicException(\sprintf(
                        'Detected a top level shorthand reference using a "$ref" property, but the value of the property is not a string.',
                    ));
                }

                $shorthandDefinition = \str_replace('#/definitions/', '', $shorthandDefinition);

                return [
                    '$ref' => "#/definitions/$shorthandDefinition",
                ];
            } elseif ($schemaProperty === '$items') {
                if (\count($shorthand) > 1) {
                    throw new LogicException(\sprintf(
                        'Shorthand %s contains a top level array property "$items", but it is not the only property!
                       \nA top level array cannot have other properties then "$items".',
                        \json_encode($shorthand)
                    ));
                }

                if (! \is_string($shorthandDefinition)) {
                    throw new LogicException(\sprintf(
                        'Detected a top level shorthand array using an "$items" property, but the value of the property is not a string.',
                    ));
                }

                if (\mb_substr($shorthandDefinition, -2) !== '[]') {
                    $shorthandDefinition .= '[]';
                }

                return self::convertShorthandStringToJsonSchema($shorthandDefinition);
            } elseif ($schemaProperty === '$title') {
                $schema['title'] = $shorthandDefinition;
                continue;
            } else {
                $schema['required'][] = $schemaProperty;
            }

            if (\is_array($shorthandDefinition)) {
                $schema['properties'][$schemaProperty] = self::convertToJsonSchema($shorthandDefinition);
            } elseif (\is_string($shorthandDefinition)) {
                $schema['properties'][$schemaProperty] = self::convertShorthandStringToJsonSchema($shorthandDefinition);
            } else {
                throw new LogicException(\sprintf(
                    'I tried to parse JSONSchema for property: "%s", but it is neither a string nor an object.',
                    $schemaProperty
                ));
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

        if ($parts[0] === 'enum') {
            return ['enum' => \array_slice($parts, 1)];
        }

        if (\mb_substr($parts[0], -2) === '[]') {
            $itemsParts = [\mb_substr($parts[0], 0, \mb_strlen($parts[0]) - 2)];
            \array_push($itemsParts, ...\array_slice($parts, 1));

            return [
                'type' => 'array',
                'items' => self::convertShorthandStringToJsonSchema(\implode('|', $itemsParts)),
            ];
        }

        switch ($parts[0]) {
            case 'string':
            case 'integer':
            case 'number':
            case 'boolean':
                $type = $parts[0];

                if (isset($parts[1]) && $parts[1] === 'null') {
                    $type = [$type, 'null'];

                    \array_splice($parts, 1, 1);
                }

                $schema = ['type' => $type];

                if (\count($parts) > 1) {
                    $parts = \array_slice($parts, 1);

                    foreach ($parts as $part) {
                        [$validationKey, $validationValue] = self::parseShorthandValidation($part);

                        $schema[$validationKey] = $validationValue;
                    }
                }

                return $schema;
            default:
                return [
                    '$ref' => '#/definitions/'.$parts[0],
                ];
        }
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

<?php

/**
 * @see       https://github.com/open-code-modeling/json-schema-to-php for the canonical source repository
 * @copyright https://github.com/open-code-modeling/json-schema-to-php/blob/master/COPYRIGHT.md
 * @license   https://github.com/open-code-modeling/json-schema-to-php/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace OpenCodeModeling\JsonSchemaToPhp\Type;

final class Type
{
    /**
     * @param array<string, mixed> $definition
     * @param string|null $name
     * @return TypeSet
     */
    public static function fromDefinition(array $definition, ?string $name = null): TypeSet
    {
        if (! isset($definition['type'])) {
            if (isset($definition['$ref'])) {
                return new TypeSet(ReferenceType::fromDefinition($definition, $name));
            }

            throw new \RuntimeException(\sprintf('The "type" is missing in schema definition for "%s"', $name));
        }

        $definitionTypes = (array) $definition['type'];

        $types = [];

        $isNullable = false;

        foreach ($definitionTypes as $jsonType) {
            $definition['type'] = $jsonType;

            switch ($jsonType) {
                case 'string':
                    $types[] = StringType::fromDefinition($definition, $name);
                    break;
                case 'number':
                    $types[] = NumberType::fromDefinition($definition, $name);
                    break;
                case 'integer':
                    $types[] = IntegerType::fromDefinition($definition, $name);
                    break;
                case 'boolean':
                    $types[] = BooleanType::fromDefinition($definition, $name);
                    break;
                case 'object':
                    $types[] = ObjectType::fromDefinition($definition, $name);
                    break;
                case 'array':
                    $types[] = ArrayType::fromDefinition($definition, $name);
                    break;
                case 'null':
                case 'Null':
                case 'NULL':
                    $isNullable = true;
                    break;
                default:
                    throw new \RuntimeException(
                        \sprintf('JSON schema type "%s" is not implemented', $definition['type'])
                    );
            }
        }

        if (\count($types) === 0) {
            throw new \RuntimeException('Could not determine type of JSON schema');
        }

        foreach ($types as $type) {
            $type->setNullable($isNullable);
        }

        return new TypeSet(...$types);
    }
}

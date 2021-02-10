<?php

/**
 * @see       https://github.com/open-code-modeling/json-schema-to-php for the canonical source repository
 * @copyright https://github.com/open-code-modeling/json-schema-to-php/blob/master/COPYRIGHT.md
 * @license   https://github.com/open-code-modeling/json-schema-to-php/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace OpenCodeModelingTest\JsonSchemaToPhp\Shorthand;

use OpenCodeModeling\JsonSchemaToPhp\Shorthand\Shorthand;
use PHPUnit\Framework\TestCase;

final class ShorthandTest extends TestCase
{
    private const SHORTHAND_TYPES = ['string', 'integer', 'number', 'boolean'];

    /**
     * @test
     */
    public function it_converts_shorthand_from_string(): void
    {
        $schema = Shorthand::convertToJsonSchema('string|format:uuid|namespace:MyService');

        $this->assertEquals(['type' => 'string', 'format' => 'uuid', 'namespace' => 'MyService'], $schema);
    }

    /**
     * @test
     */
    public function it_converts_empty_shorthand_string_to_json_schema_string(): void
    {
        $schema = Shorthand::convertToJsonSchema(['test' => '']);

        $this->assertEquals($this->jsonSchemaObject(
            ['test' => ['type' => 'string']],
            ['test']
        ), $schema);
    }

    /**
     * @test
     */
    public function it_converts_enum_shorthand_to_json_schema_string(): void
    {
        $schema = Shorthand::convertToJsonSchema(['test' => 'enum:available,blocked,bought']);

        $this->assertEquals($this->jsonSchemaObject(
            ['test' => ['enum' => ['available', 'blocked', 'bought']]],
            ['test']
        ), $schema);
    }

    /**
     * @test
     */
    public function it_converts_shorthand_type_to_json_schema(): void
    {
        foreach (self::SHORTHAND_TYPES as $type) {
            $schema = Shorthand::convertToJsonSchema(['test' => $type]);

            $this->assertEquals($this->jsonSchemaObject(
                ['test' => ['type' => $type]],
                ['test']
            ), $schema);
        }
    }

    /**
     * @test
     */
    public function it_converts_nullable_shorthand_type_to_nullable_json_schema_type(): void
    {
        foreach (self::SHORTHAND_TYPES as $type) {
            $schema = Shorthand::convertToJsonSchema(['test' => $type.'|null']);

            $this->assertEquals($this->jsonSchemaObject(
                ['test' => ['type' => [$type, 'null']]],
                ['test']
            ), $schema);
        }
    }

    /**
     * @test
     */
    public function it_converts_unknown_type_to_json_schema_ref(): void
    {
        $schema = Shorthand::convertToJsonSchema(['test' => 'User']);

        $this->assertEquals($this->jsonSchemaObject(
            ['test' => ['$ref' => '#/definitions/User']],
            ['test']
        ), $schema);
    }

    /**
     * @test
     */
    public function it_converts_array_shorthand_type_to_json_schema_array_type(): void
    {
        foreach (self::SHORTHAND_TYPES as $type) {
            $schema = Shorthand::convertToJsonSchema(['test' => $type.'[]']);

            $this->assertEquals($this->jsonSchemaObject(
                ['test' => ['type' => 'array', 'items' => ['type' => $type]]],
                ['test']
            ), $schema);
        }
    }

    /**
     * @test
     */
    public function it_converts_unknown_shorthand_array_type_to_json_schema_array_with_ref_items(): void
    {
        $schema = Shorthand::convertToJsonSchema(['test' => 'User[]']);

        $this->assertEquals($this->jsonSchemaObject(
            ['test' => ['type' => 'array', 'items' => ['$ref' => '#/definitions/User']]],
            ['test']
        ), $schema);
    }

    /**
     * @test
     */
    public function it_parses_shorthand_validation_and_adds_it_to_json_schema(): void
    {
        $schema = Shorthand::convertToJsonSchema([
            'test1' => 'string|format:email|maxLength:255',
            'test2' => 'number|minimum:0.5|maximum:10',
            'test3' => 'string|null|format:email',
            'test4' => 'boolean|default:false',
            'test5' => 'boolean|null|default:true',
        ]);

        $this->assertEquals($this->jsonSchemaObject(
            [
                'test1' => ['type' => 'string', 'format' => 'email', 'maxLength' => 255],
                'test2' => ['type' => 'number', 'minimum' => 0.5, 'maximum' => 10],
                'test3' => ['type' => ['string', 'null'], 'format' => 'email'],
                'test4' => ['type' => 'boolean', 'default' => false],
                'test5' => ['type' => ['boolean', 'null'], 'default' => true],
            ],
            ['test1', 'test2', 'test3', 'test4', 'test5']
        ), $schema);
    }

    /**
     * @test
     */
    public function it_converts_shorthand_top_level_array_to_json_schema_array(): void
    {
        $variants = ['Profile', 'Profile[]'];

        foreach ($variants as $variant) {
            $schema = Shorthand::convertToJsonSchema(['$items' => $variant]);

            $this->assertEquals(['type' => 'array', 'items' => ['$ref' => '#/definitions/Profile']], $schema);
        }
    }

    /**
     * @test
     */
    public function it_converts_top_level_reference_to_json_schema_reference(): void
    {
        $variants = ['Profile', '#/definitions/Profile'];

        foreach ($variants as $variant) {
            $schema = Shorthand::convertToJsonSchema(['$ref' => $variant]);

            $this->assertEquals(['$ref' => '#/definitions/Profile'], $schema);
        }
    }

    /**
     * @test
     */
    public function it_converts_shorthand_object_to_json_schema_object(): void
    {
        $schema = Shorthand::convertToJsonSchema([
            '$title' => 'Prospect',
            'name' => 'string|minLength:1',
            'email' => 'string|format:email',
            'age?' => 'number|minimum:0',
            'address' => [
                'zip' => 'string|minLength:1',
                'city' => 'string|minLength:1',
            ],
            'tags' => 'string[]|minLength:1',
            'searchProfile?' => [
                'roomsMin' => 'number|null|minimum:0.5',
                'roomsMax' => 'number|null|minimum:0.5',
            ],
        ]);

        $this->assertEquals(
            $this->jsonSchemaObject([
                'name' => ['type' => 'string', 'minLength' => 1],
                'email' => ['type' => 'string', 'format' => 'email'],
                'age' => ['type' => 'number', 'minimum' => 0],
                'address' => $this->jsonSchemaObject([
                    'zip' => ['type' => 'string', 'minLength' => 1],
                    'city' => ['type' => 'string', 'minLength' => 1],
                ], ['zip', 'city']),
                'tags' => ['type' => 'array', 'items' => ['type' => 'string', 'minLength' => 1]],
                'searchProfile' => $this->jsonSchemaObject([
                    'roomsMin' => ['type' => ['number', 'null'], 'minimum' => 0.5],
                    'roomsMax' => ['type' => ['number', 'null'], 'minimum' => 0.5],
                ], ['roomsMin', 'roomsMax']),
            ], ['name', 'email', 'address', 'tags'], 'Prospect'),
            $schema
        );
    }

    /**
     * @param array<string, mixed> $properties
     * @param string[] $required
     * @param null|string $title
     * @return array<string, mixed>
     */
    private function jsonSchemaObject(array $properties, array $required = [], string $title = null): array
    {
        $obj = [
            'type' => 'object',
            'properties' => $properties,
            'additionalProperties' => false,
            'required' => $required,
        ];

        if ($title) {
            $obj['title'] = $title;
        }

        return $obj;
    }
}

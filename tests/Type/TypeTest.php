<?php

/**
 * @see       https://github.com/open-code-modeling/json-schema-to-php for the canonical source repository
 * @copyright https://github.com/open-code-modeling/json-schema-to-php/blob/master/COPYRIGHT.md
 * @license   https://github.com/open-code-modeling/json-schema-to-php/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace OpenCodeModelingTest\JsonSchemaToPhp\Type;

use OpenCodeModeling\JsonSchemaToPhp\Type\IntegerType;
use OpenCodeModeling\JsonSchemaToPhp\Type\NumberType;
use OpenCodeModeling\JsonSchemaToPhp\Type\ObjectType;
use OpenCodeModeling\JsonSchemaToPhp\Type\StringType;
use OpenCodeModeling\JsonSchemaToPhp\Type\Type;
use PHPUnit\Framework\TestCase;

final class TypeTest extends TestCase
{
    /**
     * @test
     */
    public function it_supports_shorthand_definition(): void
    {
        $typeSet = Type::fromShorthand(['name' => 'string|minLength:1', '$title' => 'Person'], 'Person');

        $this->assertCount(1, $typeSet);

        /** @var ObjectType $type */
        $type = $typeSet->first();

        $properties = $type->properties();

        $this->assertArrayHasKey('name', $properties);
        $this->assertEquals('Person', $type->name());
        $this->assertEquals('Person', $type->title());
    }

    /**
     * @test
     */
    public function it_supports_string_enums_without_type(): void
    {
        $typeSet = Type::fromShorthand(['contentLanguage' => 'enum:de-DE,en-US'], 'Content');

        $this->assertCount(1, $typeSet);

        /** @var ObjectType $type */
        $type = $typeSet->first();

        $this->assertInstanceOf(ObjectType::class, $type);
        $this->assertArrayHasKey('contentLanguage', $type->properties());

        $contentLanguage = $type->properties()['contentLanguage'];
        /** @var StringType $contentLanguageType */
        $contentLanguageType = $contentLanguage->first();
        $this->assertInstanceOf(StringType::class, $contentLanguageType);
        $this->assertSame(['de-DE', 'en-US'], $contentLanguageType->enum());
    }

    /**
     * @test
     */
    public function it_supports_int_enums_without_type(): void
    {
        $typeSet = Type::fromDefinition(['enum' => [10, 20]], 'Content');

        $this->assertCount(1, $typeSet);

        /** @var IntegerType $type */
        $type = $typeSet->first();

        $this->assertInstanceOf(IntegerType::class, $type);
        $this->assertSame([10, 20], $type->enum());
    }

    /**
     * @test
     */
    public function it_supports_float_enums_without_type(): void
    {
        $typeSet = Type::fromDefinition(['enum' => [10.10, 20.20]], 'Content');

        $this->assertCount(1, $typeSet);

        /** @var NumberType $type */
        $type = $typeSet->first();

        $this->assertInstanceOf(NumberType::class, $type);
        $this->assertSame([10.10, 20.20], $type->enum());
    }
}

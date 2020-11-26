<?php

declare(strict_types=1);

namespace OpenCodeModelingTest\JsonSchemaToPhp\Type;

use OpenCodeModeling\JsonSchemaToPhp\Type\ArrayType;
use OpenCodeModeling\JsonSchemaToPhp\Type\NumberType;
use OpenCodeModeling\JsonSchemaToPhp\Type\ObjectType;
use OpenCodeModeling\JsonSchemaToPhp\Type\ReferenceType;
use OpenCodeModeling\JsonSchemaToPhp\Type\StringType;
use OpenCodeModeling\JsonSchemaToPhp\Type\Type;
use OpenCodeModeling\JsonSchemaToPhp\Type\TypeSet;
use PHPUnit\Framework\TestCase;

final class ArrayTypeTest extends TestCase
{
    /**
     * @test
     */
    public function it_supports_array_type(): void
    {
        $json = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'schema_with_array.json');
        $decodedJson = \json_decode($json, true, 512, \JSON_BIGINT_AS_STRING | \JSON_THROW_ON_ERROR);

        $typeSet = Type::fromDefinition($decodedJson);

        $this->assertCount(1, $typeSet);

        /** @var ArrayType $type */
        $type = $typeSet->first();

        $this->assertInstanceOf(ArrayType::class, $type);

        $items = $type->items();
        $this->assertCount(4, $items);

        $this->assertInstanceOf(StringType::class, $type->additionalItems()->first());

        $this->assertItemOne($items[0]);
        $this->assertItemTwo($items[1]);
        $this->assertItemThree($items[2]);
        $this->assertItemFour($items[3]);
    }

    /**
     * @test
     */
    public function it_supports_array_with_one_type(): void
    {
        $json = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'schema_with_array_one_type.json');
        $decodedJson = \json_decode($json, true, 512, \JSON_BIGINT_AS_STRING | \JSON_THROW_ON_ERROR);

        $typeSet = Type::fromDefinition($decodedJson);

        $this->assertCount(1, $typeSet);

        /** @var ArrayType $type */
        $type = $typeSet->first();

        $this->assertInstanceOf(ArrayType::class, $type);

        $items = $type->items();
        $this->assertCount(1, $items);

        $this->assertItemOne($items[0]);
    }

    /**
     * @test
     */
    public function it_supports_array_with_one_type_ref(): void
    {
        $json = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'schema_with_array_one_type_ref.json');
        $decodedJson = \json_decode($json, true, 512, \JSON_BIGINT_AS_STRING | \JSON_THROW_ON_ERROR);

        $typeSet = Type::fromDefinition($decodedJson);

        $this->assertCount(1, $typeSet);

        /** @var ArrayType $type */
        $type = $typeSet->first();

        $this->assertInstanceOf(ArrayType::class, $type);

        $items = $type->items();
        $this->assertCount(1, $items);

        /** @var ReferenceType $itemThreeType */
        $itemThreeType = $items[0]->first();
        $this->assertInstanceOf(ReferenceType::class, $itemThreeType);
        $this->assertCount(1, $itemThreeType->resolvedType());

        /** @var StringType $resolvedType */
        $resolvedType = $itemThreeType->resolvedType()->first();

        $this->assertSame(2, $resolvedType->minLength());
    }

    /**
     * @test
     */
    public function it_supports_array_shorthand_with_no_ref(): void
    {
        $json = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'schema_with_array_shorthand_no_ref.json');
        $decodedJson = \json_decode($json, true, 512, \JSON_BIGINT_AS_STRING | \JSON_THROW_ON_ERROR);

        $typeSet = Type::fromShorthand($decodedJson);

        $this->assertCount(1, $typeSet);

        /** @var ArrayType $type */
        $type = $typeSet->first();

        $this->assertInstanceOf(ArrayType::class, $type);

        $items = $type->items();
        $this->assertCount(1, $items);

        /** @var ReferenceType $item */
        $item = $items[0]->first();

        $this->assertInstanceOf(ReferenceType::class, $item);
        $this->assertNull($item->resolvedType());
    }

    private function assertItemOne(TypeSet $itemOne): void
    {
        $this->assertCount(1, $itemOne);

        /** @var NumberType $itemOneType */
        $itemOneType = $itemOne->first();
        $this->assertInstanceOf(NumberType::class, $itemOneType);
    }

    private function assertItemTwo(TypeSet $itemTwo): void
    {
        $this->assertCount(1, $itemTwo);

        /** @var StringType $itemTwoType */
        $itemTwoType = $itemTwo->first();
        $this->assertInstanceOf(StringType::class, $itemTwoType);
    }

    private function assertItemThree(TypeSet $itemThree): void
    {
        /** @var ReferenceType $address */
        $address = $itemThree->first();
        $this->assertInstanceOf(ReferenceType::class, $address);
        $this->assertCount(1, $address->resolvedType());

        /** @var ObjectType $resolvedType */
        $resolvedType = $address->resolvedType()->first();
        $this->assertInstanceOf(ObjectType::class, $resolvedType);

        $this->assertFalse($address->isRequired());
        $this->assertFalse($resolvedType->isRequired());
        $this->assertFalse($resolvedType->isNullable());

        $properties = $resolvedType->properties();
        $this->assertArrayHasKey('street_address', $properties);
        $this->assertArrayHasKey('city', $properties);
        $this->assertArrayHasKey('state', $properties);

        $stateTypeSet = $properties['state'];
        $this->assertCount(1, $stateTypeSet);

        /** @var ReferenceType $state */
        $state = $stateTypeSet->first();
        $this->assertInstanceOf(ReferenceType::class, $state);
        $this->assertTrue($state->isRequired());
        $this->assertFalse($state->isNullable());

        $resolvedTypeSet = $state->resolvedType();
        $this->assertCount(1, $resolvedTypeSet);

        /** @var StringType $state */
        $state = $resolvedTypeSet->first();
        $this->assertInstanceOf(StringType::class, $state);
        $this->assertTrue($state->isRequired());
        $this->assertFalse($state->isNullable());
        $this->assertCount(2, $state->enum());
        $this->assertContains('NY', $state->enum());
        $this->assertContains('DC', $state->enum());
    }

    private function assertItemFour(TypeSet $itemFour): void
    {
        $this->assertCount(1, $itemFour);
        /** @var StringType $itemFourType */
        $itemFourType = $itemFour->first();
        $this->assertInstanceOf(StringType::class, $itemFourType);
        $this->assertCount(4, $itemFourType->enum());

    }

}

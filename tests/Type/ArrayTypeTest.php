<?php

declare(strict_types=1);

namespace OpenCodeModelingTest\JsonSchemaToPhp\Type;

use OpenCodeModeling\JsonSchemaToPhp\Type\ArrayType;
use OpenCodeModeling\JsonSchemaToPhp\Type\NumberType;
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
        $this->assertCount(1, $itemThree);

        /** @var ReferenceType $itemThreeType */
        $itemThreeType = $itemThree->first();
        $this->assertInstanceOf(ReferenceType::class, $itemThreeType);
        $this->assertCount(1, $itemThreeType->resolvedType());

        /** @var StringType $resolvedType */
        $resolvedType = $itemThreeType->resolvedType()->first();

        $this->assertSame(2, $resolvedType->minLength());
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

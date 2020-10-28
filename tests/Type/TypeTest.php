<?php
declare(strict_types=1);

namespace OpenCodeModelingTest\JsonSchemaToPhp\Type;

use OpenCodeModeling\JsonSchemaToPhp\Type\ObjectType;
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
}

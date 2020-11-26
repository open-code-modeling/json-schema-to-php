<?php

declare(strict_types=1);

namespace OpenCodeModelingTest\JsonSchemaToPhp\Type;

use OpenCodeModeling\JsonSchemaToPhp\Type\ObjectType;
use OpenCodeModeling\JsonSchemaToPhp\Type\ReferenceType;
use OpenCodeModeling\JsonSchemaToPhp\Type\StringType;
use OpenCodeModeling\JsonSchemaToPhp\Type\Type;
use OpenCodeModeling\JsonSchemaToPhp\Type\TypeSet;
use PHPUnit\Framework\TestCase;

final class ObjectTypeTest extends TestCase
{
    /**
     * @test
     */
    public function it_supports_object_type(): void
    {
        $json = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'schema_with_object.json');
        $decodedJson = \json_decode($json, true, 512, \JSON_BIGINT_AS_STRING | \JSON_THROW_ON_ERROR);

        $typeSet = Type::fromDefinition($decodedJson);

        $this->assertCount(1, $typeSet);

        /** @var ObjectType $type */
        $type = $typeSet->first();
        $this->assertFalse($type->additionalProperties());

        $this->assertInstanceOf(ObjectType::class, $type);

        $properties = $type->properties();

        $this->assertArrayHasKey('buildingId', $properties);
        $this->assertArrayHasKey('name', $properties);

        /** @var TypeSet $buildingIdTypeSet */
        $buildingIdTypeSet = $properties['buildingId'];
        $this->assertCount(1, $buildingIdTypeSet);

        /** @var StringType $buildingId */
        $buildingId = $buildingIdTypeSet->first();

        $this->assertInstanceOf(StringType::class, $buildingId);

        $this->assertSame('buildingId', $buildingId->name());
        $this->assertSame('string', $buildingId->type());
        $this->assertSame(
            '^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$',
            $buildingId->pattern()
        );
        $this->assertTrue($buildingId->isRequired());
        $this->assertFalse($buildingId->isNullable());

        /** @var TypeSet $nameTypeSet */
        $nameTypeSet = $properties['name'];

        /** @var ReferenceType $name */
        $name = $nameTypeSet->first();
        $this->assertInstanceOf(ReferenceType::class, $name);

        $resolvedTypeSet = $name->resolvedType();

        /** @var StringType $resolvedType */
        $resolvedType = $resolvedTypeSet->first();
        $this->assertInstanceOf(StringType::class, $resolvedType);

        $this->assertSame('name', $resolvedType->name());
        $this->assertSame('string', $resolvedType->type());
        $this->assertNull($resolvedType->pattern());
        $this->assertTrue($resolvedType->isRequired());
        $this->assertTrue($resolvedType->isNullable());
    }

    /**
     * @test
     */
    public function it_supports_definition_of_objects(): void
    {
        $json = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'schema_with_objects.json');
        $decodedJson = \json_decode($json, true, 512, \JSON_BIGINT_AS_STRING | \JSON_THROW_ON_ERROR);

        $typeSet = Type::fromDefinition($decodedJson);

        $this->assertCount(1, $typeSet);

        /** @var ObjectType $type */
        $type = $typeSet->first();
        $this->assertInstanceOf(ObjectType::class, $type);
        $this->assertNull($type->additionalProperties());

        $required = $type->required();
        $this->assertCount(1, $required);
        $this->assertContains('billing_address', $required);

        $this->assertObjectsDefinitions($type);
        $this->assertObjectsProperties($type);
    }

    private function assertObjectsProperties(ObjectType $type): void
    {
        $properties = $type->properties();

        $this->assertArrayHasKey('billing_address', $properties);
        $this->assertArrayHasKey('shipping_address', $properties);

        /** @var TypeSet $billingAddressTypeSet */
        $billingAddressTypeSet = $properties['billing_address'];

        $this->assertCount(1, $billingAddressTypeSet);

        /** @var ReferenceType $billingAddress */
        $billingAddress = $billingAddressTypeSet->first();

        $this->assertInstanceOf(ReferenceType::class, $billingAddress);
        $this->assertAddressObject($billingAddress, true);

        /** @var TypeSet $shippingAddressTypeSet */
        $shippingAddressTypeSet = $properties['shipping_address'];

        $this->assertCount(1, $shippingAddressTypeSet);

        /** @var ReferenceType $shippingAddress */
        $shippingAddress = $shippingAddressTypeSet->first();

        $this->assertInstanceOf(ReferenceType::class, $shippingAddress);
        $this->assertAddressObject($shippingAddress, false);
    }

    private function assertObjectsDefinitions(ObjectType $type): void
    {
        $definitions = $type->definitions();
        $this->assertCount(2, $definitions);
        $this->assertArrayHasKey('address', $definitions);
        $this->assertArrayHasKey('state', $definitions);

        /** @var TypeSet $addressTypeSet */
        $addressTypeSet = $definitions['address'];
        $this->assertCount(1, $addressTypeSet);

        /** @var ObjectType $address */
        $address = $addressTypeSet->first();
        $this->assertFalse($address->isRequired());
        $this->assertFalse($address->isNullable());

        $stateTypeSet = $definitions['state'];
        $this->assertCount(1, $stateTypeSet);

        /** @var StringType $state */
        $state = $stateTypeSet->first();
        $this->assertInstanceOf(StringType::class, $state);
        $this->assertFalse($state->isRequired());
        $this->assertFalse($state->isNullable());
        $this->assertCount(2, $state->enum());
        $this->assertContains('NY', $state->enum());
        $this->assertContains('DC', $state->enum());
    }

    private function assertAddressObject(ReferenceType $address, bool $required): void
    {
        $this->assertCount(1, $address->resolvedType());

        /** @var ObjectType $resolvedType */
        $resolvedType = $address->resolvedType()->first();
        $this->assertInstanceOf(ObjectType::class, $resolvedType);

        $this->assertSame($required, $address->isRequired());
        $this->assertSame($required, $resolvedType->isRequired());
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
}

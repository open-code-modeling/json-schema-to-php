<?php

/**
 * @see       https://github.com/open-code-modeling/json-schema-to-php for the canonical source repository
 * @copyright https://github.com/open-code-modeling/json-schema-to-php/blob/master/COPYRIGHT.md
 * @license   https://github.com/open-code-modeling/json-schema-to-php/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace OpenCodeModelingTest\JsonSchemaToPhp\Type;

use OpenCodeModeling\JsonSchemaToPhp\Type\ArrayType;
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
        $json = \file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'schema_with_object.json');
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
    public function it_supports_definition_of_objects_shorthand(): void
    {
        $json = \file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'schema_with_objects_shorthand.json');
        $decodedJson = \json_decode($json, true, 512, \JSON_BIGINT_AS_STRING | \JSON_THROW_ON_ERROR);

        $typeSet = Type::fromShorthand($decodedJson);

        $this->assertCount(1, $typeSet);

        /** @var ObjectType $type */
        $type = $typeSet->first();
        $this->assertInstanceOf(ObjectType::class, $type);
        $this->assertFalse($type->additionalProperties());

        $required = $type->required();
        $this->assertCount(4, $required);
        $this->assertContains('uuid', $required);
        $this->assertContains('salutation', $required);
        $this->assertContains('billing_address', $required);
        $this->assertContains('shipping_address', $required);

        $properties = $type->properties();
        $this->assertCount(4, $properties);
        $this->assertArrayHasKey('uuid', $properties);
        $this->assertArrayHasKey('salutation', $properties);
        $this->assertArrayHasKey('shipping_address', $properties);
        $this->assertArrayHasKey('shipping_address', $properties);

        /** @var StringType $uuid */
        $uuid = $properties['uuid']->first();
        $this->assertInstanceOf(StringType::class, $uuid);
        $this->assertSame('uuid', $uuid->format());
        $this->assertSame([], $uuid->custom());

        /** @var StringType $salutation */
        $salutation = $properties['salutation']->first();
        $this->assertInstanceOf(StringType::class, $salutation);
        $this->assertSame(['MR', 'MRS'], $salutation->enum());
        $this->assertSame(['namespace' => 'Contact'], $salutation->custom());

        /** @var ReferenceType $billingAddress */
        $billingAddress = $properties['billing_address']->first();
        $this->assertInstanceOf(ReferenceType::class, $billingAddress);
        $this->assertSame(['namespace' => 'Order'], $billingAddress->custom());

        /** @var ArrayType $shippingAddress */
        $shippingAddress = $properties['shipping_address']->first();
        $this->assertInstanceOf(ArrayType::class, $shippingAddress);
        $this->assertSame([], $shippingAddress->custom());

        /** @var ReferenceType $address */
        $address = $shippingAddress->items()[0]->first();
        $this->assertInstanceOf(ReferenceType::class, $address);
        $this->assertSame(['namespace' => 'Order'], $address->custom());
    }

    /**
     * @test
     */
    public function it_supports_definition_of_objects_shorthand_type_ns(): void
    {
        $json = \file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'schema_with_objects_shorthand_type_ns.json');
        $decodedJson = \json_decode($json, true, 512, \JSON_BIGINT_AS_STRING | \JSON_THROW_ON_ERROR);

        $typeSet = Type::fromShorthand($decodedJson);

        $this->assertCount(1, $typeSet);

        /** @var ObjectType $type */
        $type = $typeSet->first();
        $this->assertInstanceOf(ObjectType::class, $type);
        $this->assertFalse($type->additionalProperties());

        $required = $type->required();
        $this->assertCount(4, $required);
        $this->assertContains('uuid', $required);
        $this->assertContains('salutation', $required);
        $this->assertContains('billing_address', $required);
        $this->assertContains('shipping_address', $required);

        $properties = $type->properties();
        $this->assertCount(4, $properties);
        $this->assertArrayHasKey('uuid', $properties);
        $this->assertArrayHasKey('salutation', $properties);
        $this->assertArrayHasKey('shipping_address', $properties);
        $this->assertArrayHasKey('shipping_address', $properties);

        /** @var StringType $uuid */
        $uuid = $properties['uuid']->first();
        $this->assertInstanceOf(StringType::class, $uuid);
        $this->assertSame('uuid', $uuid->format());
        $this->assertSame(['namespace' => '/'], $uuid->custom());

        /** @var StringType $salutation */
        $salutation = $properties['salutation']->first();
        $this->assertInstanceOf(StringType::class, $salutation);
        $this->assertSame(['MR', 'MRS'], $salutation->enum());
        $this->assertSame(['namespace' => '/Contact'], $salutation->custom());

        /** @var ReferenceType $billingAddress */
        $billingAddress = $properties['billing_address']->first();
        $this->assertInstanceOf(ReferenceType::class, $billingAddress);
        $this->assertSame(['namespace' => 'Order'], $billingAddress->custom());

        /** @var ArrayType $shippingAddress */
        $shippingAddress = $properties['shipping_address']->first();
        $this->assertInstanceOf(ArrayType::class, $shippingAddress);
        $this->assertSame([], $shippingAddress->custom());

        /** @var ReferenceType $address */
        $address = $shippingAddress->items()[0]->first();
        $this->assertInstanceOf(ReferenceType::class, $address);
        $this->assertSame(['namespace' => 'Order'], $address->custom());
    }

    /**
     * @test
     */
    public function it_supports_definition_of_objects(): void
    {
        $json = \file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'schema_with_objects.json');
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
        $this->assertSame(['namespace' => 'Order'], $billingAddress->custom());

        /** @var TypeSet $shippingAddressTypeSet */
        $shippingAddressTypeSet = $properties['shipping_address'];

        $this->assertCount(1, $shippingAddressTypeSet);

        /** @var ReferenceType $shippingAddress */
        $shippingAddress = $shippingAddressTypeSet->first();

        $this->assertInstanceOf(ReferenceType::class, $shippingAddress);
        $this->assertAddressObject($shippingAddress, false);
        $this->assertSame(['namespace' => 'Order'], $shippingAddress->custom());
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
        $this->assertSame(['namespace' => 'Address'], $resolvedType->custom());

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
        $this->assertSame(['namespace' => 'Address'], $state->custom());

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

        $streetAddressTypeSet = $properties['street_address'];
        $this->assertCount(1, $streetAddressTypeSet);

        /** @var StringType $streetAddress */
        $streetAddress = $streetAddressTypeSet->first();
        $this->assertInstanceOf(StringType::class, $streetAddress);
        $this->assertSame('street_address', $streetAddress->name());

        $this->assertSame(['namespace' => 'Address'], $streetAddress->custom());
    }

    /**
     * @test
     */
    public function it_supports_definition_of_objects_shorthand_nested_ns(): void
    {
        $json = \file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'schema_with_objects_shorthand_nested_ns.json');
        $decodedJson = \json_decode($json, true, 512, \JSON_BIGINT_AS_STRING | \JSON_THROW_ON_ERROR);

        $typeSet = Type::fromShorthand($decodedJson, 'checkout', '/Order', '/Shipping');

        $this->assertCount(1, $typeSet);

        /** @var ObjectType $type */
        $type = $typeSet->first();
        $this->assertInstanceOf(ObjectType::class, $type);
        $this->assertFalse($type->additionalProperties());

        $required = $type->required();
        $this->assertCount(4, $required);
        $this->assertContains('order', $required);
        $this->assertContains('salutation', $required);
        $this->assertContains('name', $required);
        $this->assertContains('items', $required);

        $properties = $type->properties();
        $this->assertCount(4, $properties);
        $this->assertArrayHasKey('order', $properties);
        $this->assertArrayHasKey('salutation', $properties);
        $this->assertArrayHasKey('name', $properties);
        $this->assertArrayHasKey('items', $properties);

        /** @var ObjectType $order */
        $order = $properties['order']->first();
        $this->assertInstanceOf(ObjectType::class, $order);
        // implicit object Order is in namespace /Shipping and value objects with no namespace also in /Shipping
        $this->assertSame(['namespace' => '/Shipping', 'voNamespace' => '/Shipping'], $order->custom());

        $subProperties = $order->properties();
        $this->assertCount(3, $subProperties);
        $this->assertArrayHasKey('billing_address', $subProperties);
        $this->assertArrayHasKey('shipping_address', $subProperties);
        $this->assertArrayHasKey('payment_address', $subProperties);

        /** @var StringType $paymentAddress */
        $paymentAddress = $subProperties['payment_address']->first();
        $this->assertInstanceOf(StringType::class, $paymentAddress);
        // value object defines it's own namespace
        $this->assertSame(['namespace' => 'Payment'], $paymentAddress->custom());

        /** @var ReferenceType $billingAddress */
        $billingAddress = $subProperties['billing_address']->first();
        $this->assertInstanceOf(ReferenceType::class, $billingAddress);
        // reference value object use defined voNamespace because of missing namespace definition
        $this->assertSame(['namespace' => '/Shipping'], $billingAddress->custom());
        $this->assertSame('#/definitions/Shipping/Address', $billingAddress->ref());

        /** @var ArrayType $shippingAddress */
        $shippingAddress = $subProperties['shipping_address']->first();
        $this->assertInstanceOf(ArrayType::class, $shippingAddress);
        // implicit array value object uses defined voNamespace
        $this->assertSame(['namespace' => '/Shipping'], $shippingAddress->custom());

        /** @var ReferenceType $items */
        $items = $shippingAddress->items()[0]->first();
        $this->assertInstanceOf(ReferenceType::class, $items);
        // item defines it's own namespace
        $this->assertSame(['namespace' => '/Order'], $items->custom());
        $this->assertSame('#/definitions/Order/Address', $items->ref());

        /** @var StringType $salutation */
        $salutation = $properties['salutation']->first();
        $this->assertInstanceOf(StringType::class, $salutation);
        $this->assertSame(['MR', 'MRS'], $salutation->enum());
        $this->assertSame(['namespace' => '/Contact'], $salutation->custom());

        /** @var StringType $name */
        $name = $properties['name']->first();
        $this->assertInstanceOf(StringType::class, $name);
        // value objects with no namespace are in voNamespace /Shipping
        $this->assertSame(['namespace' => '/Shipping'], $name->custom());

        /** @var ArrayType $items */
        $items = $properties['items']->first();
        $this->assertInstanceOf(ArrayType::class, $items);
        // implicit array value object uses defined voNamespace
        $this->assertSame(['namespace' => '/Shipping'], $items->custom());
    }
}

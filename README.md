# JSON Schema to PHP

Parses JSON schema and provides an API to easily generate code from JSON schema.

## Installation

```bash
$ composer require open-code-modeling/json-schema-to-php --dev
```

## Usage

Consider you have this JSON schema.

```json
{
    "type": "object",
    "required": ["buildingId", "name"],
    "additionalProperties": false,
    "definitions": {
        "name": {
            "type": ["string", "null"]
        }
    },

    "properties": {
        "buildingId": {
            "type": "string",
            "pattern": "^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$"
        },
        "name": {
            "$ref": "#/definitions/name"
        }
    }
}
```

Create a TypeSet definition from parsed JSON:

```php
$decodedJson = \json_decode($jsonSchema, true);

$typeSet = Type::fromDefinition($decodedJson);

/** @var ObjectType $type */
$type = $typeSet->first();

$type->additionalProperties(); // false

$properties = $type->properties();

/** @var TypeSet $buildingIdTypeSet */
$buildingIdTypeSet = $properties['buildingId'];

/** @var StringType $buildingId */
$buildingId = $buildingIdTypeSet->first();

$buildingId->name(); // buildingId
$buildingId->type(); // string
$buildingId->pattern(); // ^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$
$buildingId->isRequired(); // true
$buildingId->isNullable(); // false

/** @var TypeSet $nameTypeSet */
$nameTypeSet = $properties['name'];

/** @var ReferenceType $name */
$name = $nameTypeSet->first();

$resolvedTypeSet = $name->resolvedType();

/** @var StringType $resolvedType */
$resolvedType = $resolvedTypeSet->first();

$resolvedType->name(); // name
$resolvedType->type(); // string
$resolvedType->isRequired(); // true
$resolvedType->isNullable(); // true

// ...
```

See `OpenCodeModeling\JsonSchemaToPhp\Type` classes and tests for more information.

<?php

/**
 * @see       https://github.com/open-code-modeling/json-schema-to-php for the canonical source repository
 * @copyright https://github.com/open-code-modeling/json-schema-to-php/blob/master/COPYRIGHT.md
 * @license   https://github.com/open-code-modeling/json-schema-to-php/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace OpenCodeModeling\JsonSchemaToPhp\Exception;

use LogicException as PhpLogicException;

class LogicException extends PhpLogicException implements ExceptionInterface
{
}

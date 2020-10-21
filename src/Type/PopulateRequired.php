<?php

/**
 * @see       https://github.com/open-code-modeling/json-schema-to-php for the canonical source repository
 * @copyright https://github.com/open-code-modeling/json-schema-to-php/blob/master/COPYRIGHT.md
 * @license   https://github.com/open-code-modeling/json-schema-to-php/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace OpenCodeModeling\JsonSchemaToPhp\Type;

trait PopulateRequired
{
    /**
     * @var array<string>
     */
    protected array $required = [];

    public function populateRequired(string $key): void
    {
        if (! \is_array($this->$key)) {
            return;
        }

        foreach ($this->required as $requiredName) {
            switch (true) {
                case \is_scalar($this->$key):
                    break;
                case $this->$key[$requiredName] instanceof RequiredAware:
                    $this->$key[$requiredName]->setIsRequired(true);
                    break;
                default:
                    throw new \RuntimeException(
                        \sprintf(
                            'Property "%s" of type "%s" does not support require.',
                            $requiredName,
                            \get_class($this->$key[$requiredName])
                        )
                    );
            }
        }
    }
}

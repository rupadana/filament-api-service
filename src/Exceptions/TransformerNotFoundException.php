<?php

declare(strict_types=1);

namespace Rupadana\ApiService\Exceptions;

use Exception;

final class TransformerNotFoundException extends Exception
{
    public function __construct(string $transformer)
    {
        parent::__construct("Transformer {$transformer} not found");
    }
}

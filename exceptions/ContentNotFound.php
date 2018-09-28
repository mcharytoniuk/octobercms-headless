<?php

namespace Newride\Headless\Exceptions;

use RuntimeException;
use Throwable;

class ContentNotFound extends RuntimeException
{
    public function __construct(string $key, array $data, int $code = 0, Throwable $previous = null)
    {
        $keys = array_keys($data);
        sort($keys);

        parent::__construct(
            sprintf(
                'Content not found: "%s". Available keys: "%s"',
                $key,
                implode(', ', $keys)
            ),
            $code,
            $previous
        );
    }
}

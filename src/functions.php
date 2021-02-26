<?php

declare(strict_types=1);

namespace App;

function nullify(string | int | float | bool | null $value): string | int | float | bool | null
{
    if (null === $value) {
        return null;
    }

    if (\is_string($value) && '' === \trim($value)) {
        return null;
    }

    return $value;
}

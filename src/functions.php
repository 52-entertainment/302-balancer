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

function cast_if_not_null(mixed $value, string $type): mixed
{
    if (null === $value) {
        return $value;
    }

    \settype($value, $type);
    return $value;
}

<?php

namespace App\Support\Database;

final class SqlList
{
    /**
     * @param  array<int, string>  $values
     */
    public static function inQuoted(array $values): string
    {
        return implode(', ', array_map(
            static fn (string $value): string => "'".str_replace("'", "''", $value)."'",
            $values,
        ));
    }
}

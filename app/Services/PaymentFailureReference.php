<?php

namespace App\Services;

use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use Throwable;

class PaymentFailureReference
{
    public static function for(string $step, Throwable $exception): string
    {
        $class = substr(class_basename($exception), 0, 16);
        $databaseCode = $exception instanceof QueryException && isset($exception->errorInfo[1])
            ? '-' . (int) $exception->errorInfo[1]
            : '';

        return $step . '-' . $class . $databaseCode . '-' . Str::upper(Str::random(8));
    }
}

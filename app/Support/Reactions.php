<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

class Reactions
{
    private static ?bool $isEnabled = null;

    public static function isEnabled(): bool
    {
        if (self::$isEnabled !== null) {
            return self::$isEnabled;
        }

        self::$isEnabled = Schema::hasTable('reactions');

        return self::$isEnabled;
    }
}

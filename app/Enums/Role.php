<?php

namespace App\Enums;

enum Role: string
{
    case OWNER = 'owner';
    case EDITOR = 'editor';
    case VIEWER = 'viewer';

    public function name()
    {
        return ucfirst($this->value);
    }

    public static function values()
    {
        return array_column(static::cases(), 'value');
    }
}

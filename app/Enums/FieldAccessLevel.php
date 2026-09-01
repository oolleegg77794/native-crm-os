<?php

declare(strict_types=1);

namespace App\Enums;

enum FieldAccessLevel: string
{
    case WRITE = 'write';
    case READ = 'read';
    case HIDDEN = 'hidden';

    public function canRead(): bool
    {
        return $this !== self::HIDDEN;
    }

    public function canWrite(): bool
    {
        return $this === self::WRITE;
    }
}

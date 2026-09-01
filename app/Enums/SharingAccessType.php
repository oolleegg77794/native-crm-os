<?php

declare(strict_types=1);

namespace App\Enums;

enum SharingAccessType: string
{
    case PUBLIC_READ_WRITE = 'public_read_write';
    case PUBLIC_READ_ONLY = 'public_read_only';
    case PRIVATE = 'private';
}

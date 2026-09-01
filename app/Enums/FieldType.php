<?php

declare(strict_types=1);

namespace App\Enums;

enum FieldType: string
{
    case STRING = 'string';
    case TEXT = 'text';
    case LONGTEXT = 'longtext';
    case INTEGER = 'integer';
    case DECIMAL = 'decimal';
    case CURRENCY = 'currency';
    case PERCENT = 'percent';
    case BOOLEAN = 'boolean';
    case DATE = 'date';
    case DATETIME = 'datetime';
    case TIME = 'time';
    case EMAIL = 'email';
    case PHONE = 'phone';
    case URL = 'url';
    case PICKLIST = 'picklist';
    case MULTISELECT = 'multiselect';
    case LOOKUP = 'lookup';
    case MULTI_LOOKUP = 'multi_lookup';
    case AUTONUMBER = 'autonumber';
    case JSON = 'json';

    public function isNumeric(): bool
    {
        return in_array($this, [self::INTEGER, self::DECIMAL, self::CURRENCY, self::PERCENT], true);
    }

    public function isDateOrTime(): bool
    {
        return in_array($this, [self::DATE, self::DATETIME, self::TIME], true);
    }

    public function isLookup(): bool
    {
        return in_array($this, [self::LOOKUP, self::MULTI_LOOKUP], true);
    }
}

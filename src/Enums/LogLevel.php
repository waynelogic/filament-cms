<?php

namespace Waynelogic\FilamentCms\Enums;

use Filament\Support\Contracts\HasColor;

enum LogLevel : string implements HasColor
{
    case DEBUG = 'debug';
    case INFO = 'info';
    case NOTICE = 'notice';
    case WARNING = 'warning';
    case ERROR = 'error';
    case CRITICAL = 'critical';
    case ALERT = 'alert';
    case EMERGENCY = 'emergency';
    case PROCESSED = 'processed';
    case FAILED = 'failed';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::INFO => 'primary',
            self::DEBUG, self::NOTICE, self::PROCESSED => 'info',
            self::WARNING, self::FAILED => 'warning',
            self::ERROR, self::CRITICAL, self::ALERT, self::EMERGENCY => 'danger',
        };
    }
}

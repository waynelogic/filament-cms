<?php

namespace Waynelogic\FilamentCms\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum Mailer : string implements HasLabel
{
    case SMTP = 'smtp';
    case LOG = 'log';
    case ARRAY = 'array';
    case SENDMAIL = 'sendmail';
    case MAILGUN = 'mailgun';
    case SES = 'ses';
    case SES_V2 = 'ses-v2';
    case POSTMARK = 'postmark';
    case RESEND = 'resend';
    case FAILOVER = 'failover';
    case ROUNDROBIN = 'roundrobin';
    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::SMTP => 'SMTP',
            self::SENDMAIL => 'Sendmail',
            self::MAILGUN => 'Mailgun',
            self::SES => 'SES',
            self::SES_V2 => 'SES V2',
            self::POSTMARK => 'Postmark',
            self::RESEND => 'Resend',
            self::LOG => 'Журнал событий',
            self::ARRAY => 'Массив',
            self::FAILOVER => 'Failover',
            self::ROUNDROBIN => 'Roundrobin',
        };
    }
}

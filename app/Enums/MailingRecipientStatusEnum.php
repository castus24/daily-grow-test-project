<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * Статус получения рассылки
 *
 * @uses self::PENDING В процессе
 * @uses self::SENT Отправлено
 * @uses self::FAILED Ошибка
 */
final class MailingRecipientStatusEnum extends Enum
{
    const PENDING = 'pending';
    const SENT = 'sent';
    const FAILED = 'failed';
}

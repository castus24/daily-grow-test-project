<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * Статус отправки рассылки
 *
 * @uses self::DRAFT Черновик
 * @uses self::SCHEDULED Запланировано
 * @uses self::SENT Отправлено
 * @uses self::FAILED Ошибка
 */
final class MailingStatusEnum extends Enum
{
    const DRAFT = 'draft';
    const SCHEDULED = 'scheduled';
    const SENT = 'sent';
    const FAILED = 'failed';
}

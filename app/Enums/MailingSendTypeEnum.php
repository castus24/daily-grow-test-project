<?php
namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * Тип отравки рассылки
 *
 * @uses self::NOW Сразу
 * @uses self::AUTO Автоматическая рассылка
 * @uses self::REGULAR Регулярная рассылка, раз в указанное время
 */
final class MailingSendTypeEnum extends Enum
{
    const NOW = 'now';
    const AUTO = 'auto';
    const REGULAR = 'regular';
}

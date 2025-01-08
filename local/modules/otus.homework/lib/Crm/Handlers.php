<?php

namespace Otus\Homework\Crm;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class Handlers
{
    public static function updateTabs(Event $event): EventResult
    {
        // Логируем начало работы обработчика
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/handlers_log.txt', "=== Старт updateTabs ===\n", FILE_APPEND);

        $tabs = $event->getParameter('tabs');
        $entityTypeId = $event->getParameter('entityTypeID');

        // Логируем параметры события
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/handlers_log.txt', "Получен entityTypeID={$entityTypeId}\n", FILE_APPEND);

        // Проверяем, что это сделка
        if ($entityTypeId !== \CCrmOwnerType::Deal) {
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/handlers_log.txt', "Это не сделка, вкладка не добавлена\n", FILE_APPEND);
            return new EventResult(EventResult::SUCCESS, ['tabs' => $tabs]);
        }

        $dealId = (int)$event->getParameter('entityID');
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/handlers_log.txt', "Получен dealId={$dealId}\n", FILE_APPEND);

        // Добавляем вкладку
        $tabs[] = [
            'id' => 'test', // Уникальный ID вкладки
            'name' => 'Тестовая вкладка', // Название вкладки
            'loader' => [
                'serviceUrl' => '/local/modules/otus.homework/install/components/otus.homework/otus.grid/lazyload.ajax.php'
                    . '?dealId=' . $dealId
                    . '&site=' . \SITE_ID
                    . '&' . bitrix_sessid_get(),
                'componentData' => [
                    'template' => '',
                    'params' => [
                        'DEAL_ID' => $dealId,
                    ],
                ],
            ],
        ];

        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/handlers_log.txt', "Вкладка добавлена: " . print_r($tabs, true) . "\n", FILE_APPEND);

        return new EventResult(EventResult::SUCCESS, ['tabs' => $tabs]);
    }
}

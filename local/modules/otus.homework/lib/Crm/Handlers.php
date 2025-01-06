<?php
namespace Otus\Homework\Crm;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class Handlers
{
    public static function updateTabs(Event $event): EventResult
    {
        $tabs = $event->getParameter('tabs');
        $entityTypeId = $event->getParameter('entityTypeID');

        // Проверяем, что это именно сделка
        if ($entityTypeId !== \CCrmOwnerType::Deal) {
            return new EventResult(EventResult::SUCCESS, ['tabs' => $tabs]);
        }

        $dealId = (int)$event->getParameter('entityID'); // ID сделки

        // Добавляем нашу вкладку
        $tabs[] = [
            'id' => 'test', // Уникальный ID вкладки
            'name' => 'Тестовая вкладка', // Название вкладки
            'loader' => [
                'serviceUrl' => '/bitrix/components/otus.homework/otus.grid/lazyload.ajax.php'
                    . '?dealId=' . $dealId
                    . '&site=' . \SITE_ID
                    . '&' . bitrix_sessid_get(),
                'componentData' => [
                    'template' => '',
                    'params' => [
                        'dealId' => $dealId, // Передаём ID сделки в компонент
                    ],
                ],
            ],
        ];

        return new EventResult(EventResult::SUCCESS, ['tabs' => $tabs]);
    }
}

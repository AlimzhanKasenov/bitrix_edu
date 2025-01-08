<?php

use Bitrix\Main\Application;

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

// Логируем начало
file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/lazyload_log.txt', "=== Старт lazyload.ajax.php ===\n", FILE_APPEND);

$dealId = (int)Application::getInstance()->getContext()->getRequest()->get('dealId');
file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/lazyload_log.txt', "Получен dealId={$dealId}\n", FILE_APPEND);

// Буферизация вывода
ob_start();

global $APPLICATION;
$APPLICATION->IncludeComponent(
    'otus.homework:otus.grid', // Название компонента
    '.default',                // Шаблон компонента
    [
        'DEAL_ID' => $dealId,  // Параметры
    ]
);

$response = ob_get_clean();
file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/lazyload_log.txt', "Содержимое ответа: {$response}\n", FILE_APPEND);

// Отправляем вывод
echo $response;

file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/lazyload_log.txt', "=== Завершение lazyload.ajax.php ===\n", FILE_APPEND);

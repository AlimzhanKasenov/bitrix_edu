<?php

declare(strict_types=1);

use Bitrix\Main\Application;
use Bitrix\Main\Loader;

define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC', 'Y');
define('NO_AGENT_CHECK', true);
define('PUBLIC_AJAX_MODE', true);
define('DisableEventsCheck', true);

$siteID = isset($_REQUEST['site']) ? mb_substr(preg_replace('/[^a-z0-9_]/i', '', $_REQUEST['site']), 0, 2) : '';
if ($siteID !== '') {
    define('SITE_ID', $siteID);
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die('Access denied.');
}

// Проверка модулей
if (!Loader::includeModule('crm') || !Loader::includeModule('iblock') || !check_bitrix_sessid()) {
    die('Module load error or access denied.');
}

// Получаем ID сделки
$dealId = (int)Application::getInstance()->getContext()->getRequest()->get('dealId');

// Логирование для отладки
file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/lazyload_log.txt', "Запрос получен: dealId={$dealId}\n", FILE_APPEND);

// Вызов компонента
$APPLICATION->IncludeComponent(
    'otus.homework:otus.grid',
    '.default',
    [
        'DEAL_ID' => $dealId,
    ]
);

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');

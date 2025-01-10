<?php
use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;

// Подключаем модуль инфоблоков (до регистрации события!)
Loader::includeModule('iblock');

// Регистрируем обработчик
$eventManager = EventManager::getInstance();
$eventManager->addEventHandler(
    'iblock',
    'OnIBlockPropertyBuildList',
    ['CustomProperty\ProcedureSelector', 'GetUserTypeDescription']
);

// Ваш автозагрузчик для класса ProcedureSelector
spl_autoload_register(function ($class) {
    $filePath = $_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/classes/"
        . str_replace("\\", "/", $class) . ".php";
    if (file_exists($filePath)) {
        require_once $filePath;
    }
});

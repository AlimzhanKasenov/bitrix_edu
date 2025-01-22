<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$logFile = $_SERVER['DOCUMENT_ROOT'] . '/lazyload_log.txt';
file_put_contents($logFile, "=== [component.php] Старт ===\n", FILE_APPEND);

class OtusGridComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        $logFile = $_SERVER['DOCUMENT_ROOT'] . '/lazyload_log.txt';
        file_put_contents($logFile, "[component.php] Параметры: " . print_r($this->arParams, true) . "\n", FILE_APPEND);

        if (!\Bitrix\Main\Loader::includeModule('iblock')) {
            file_put_contents($logFile, "[component.php] Ошибка: модуль Инфоблоки не подключен\n", FILE_APPEND);
            ShowError('Модуль Инфоблоки не подключен');
            return;
        } else {
            file_put_contents($logFile, "[component.php] Модуль Инфоблоки успешно подключен\n", FILE_APPEND);
        }

        // Настройка фильтра для выборки данных из инфоблока
        $filter = [
            'IBLOCK_ID' => 16, // Убедитесь, что это правильный ID инфоблока
            'ACTIVE' => 'Y',   // Только активные элементы
        ];

        // Поля для выборки
        $select = ['ID', 'NAME', 'PROPERTY_FIO'];

        // Получение данных из инфоблока
        $res = \CIBlockElement::GetList([], $filter, false, false, $select);

        $this->arResult['ITEMS'] = [];
        while ($item = $res->GetNext()) {
            file_put_contents($logFile, "[component.php] Элемент: " . print_r($item, true) . "\n", FILE_APPEND);
            $this->arResult['ITEMS'][] = $item;
        }

        // Логируем количество найденных элементов
        file_put_contents($logFile, "[component.php] Найдено элементов: " . count($this->arResult['ITEMS']) . "\n", FILE_APPEND);

        // Если данных нет, выводим сообщение в лог
        if (empty($this->arResult['ITEMS'])) {
            file_put_contents($logFile, "[component.php] Внимание: Данных нет\n", FILE_APPEND);
        }

        // Подключаем шаблон
        file_put_contents($logFile, "[component.php] Подключаем шаблон\n", FILE_APPEND);
        $this->includeComponentTemplate();
        file_put_contents($logFile, "=== [component.php] Конец ===\n", FILE_APPEND);
    }
}

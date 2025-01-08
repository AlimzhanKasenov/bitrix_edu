<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class OtusGridComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        // Логируем старт компонента
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/lazyload_log.txt', "=== Старт OtusGridComponent ===\n", FILE_APPEND);

        $dealId = $this->arParams['DEAL_ID'];
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/lazyload_log.txt', "Получен параметр DEAL_ID={$dealId}\n", FILE_APPEND);

        // Подготовка данных для шаблона
        $this->arResult['TEST'] = "Тестовые данные для DEAL_ID={$dealId}";
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/lazyload_log.txt', "Передача данных в шаблон: " . print_r($this->arResult, true) . "\n", FILE_APPEND);

        // Подключение шаблона
        $this->includeComponentTemplate();

        // Логируем завершение компонента
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/lazyload_log.txt', "=== Завершение OtusGridComponent ===\n", FILE_APPEND);
    }
}

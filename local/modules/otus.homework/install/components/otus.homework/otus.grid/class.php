<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class OtusGridComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        // Логируем вызов компонента
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/lazyload_log.txt', "Компонент вызван\n", FILE_APPEND);

        // Тест: выведем текст, чтобы проверить работу
        $this->arResult['TEST_TEXT'] = 'Компонент otus.grid работает! DEAL_ID = ' . htmlspecialcharsbx($this->arParams['DEAL_ID']);
        $this->includeComponentTemplate();
    }
}

<?php
namespace CustomProperty;

use Bitrix\Main\Loader;

class ProcedureSelector
{
    public static function GetUserTypeDescription()
    {
        return [
            // Чтобы свойство хранило ID элемента, нужно указать PROPERTY_TYPE = 'E'
            'PROPERTY_TYPE'        => 'E',
            'USER_TYPE'            => 'procedure_selector',
            'DESCRIPTION'          => 'Запись на процедуру (привязка к элементам)',
            // Методы, отвечающие за вывод/редактирование в админке
            'GetPropertyFieldHtml'     => [__CLASS__, 'GetPropertyFieldHtml'],
            'GetAdminListViewHTML'     => [__CLASS__, 'GetAdminListViewHTML'],
            // Методы, отвечающие за вывод/редактирование в публичной части
            'GetPublicViewHTML'        => [__CLASS__, 'GetPublicViewHTML'],
            'GetPublicEditHTML'        => [__CLASS__, 'GetPublicEditHTML'],
        ];
    }

    /**
     * Админка: редактирование в форме элемента
     */
    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        Loader::includeModule('iblock');

        // Текущее значение (ID связанного элемента)
        $selectedElementId = (int)$value['VALUE'];

        // Готовим select
        $html = '<select name="' . $strHTMLControlName["VALUE"] . '">';
        $html .= '<option value="">(не выбрано)</option>';

        // Например, выбираем элементы из ИБ "Процедуры" (IBLOCK_ID = 2)
        $rs = \CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            ['IBLOCK_ID' => 17, 'ACTIVE' => 'Y'],
            false,
            false,
            ['ID', 'NAME']
        );
        while ($arElem = $rs->Fetch()) {
            $selected = ($arElem['ID'] == $selectedElementId) ? 'selected' : '';
            $html .= '<option value="'.$arElem['ID'].'" '.$selected.'>'.htmlspecialchars($arElem['NAME']).'</option>';
        }

        $html .= '</select>';
        return $html;
    }

    /**
     * Админка: вывод в списке
     */
    public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
    {
        $elementId = (int)$value['VALUE'];
        if (!$elementId) {
            return '&nbsp;';
        }

        // Попробуем получить название элемента
        Loader::includeModule('iblock');
        $arElem = \CIBlockElement::GetList(
            [],
            ['ID' => $elementId],
            false,
            false,
            ['ID', 'NAME']
        )->Fetch();

        return $arElem ? htmlspecialchars($arElem['NAME']) : $elementId;
    }

    /**
     * Публичная часть: вывод (view)
     */
    public static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
    {
        $elementId = (int)$value['VALUE'];
        if (!$elementId) {
            return '<span style="color: gray;">Нет данных</span>';
        }

        Loader::includeModule('iblock');
        $arElem = \CIBlockElement::GetList(
            [],
            ['ID' => $elementId],
            false,
            false,
            ['ID','NAME']
        )->Fetch();

        if ($arElem) {
            return '<span>' . htmlspecialchars($arElem['NAME']) . '</span>';
        }

        return '<span style="color: gray;">Нет данных</span>';
    }

    /**
     * Публичная часть: редактирование (edit)
     */
    public static function GetPublicEditHTML($arProperty, $value, $strHTMLControlName)
    {
        Loader::includeModule('iblock');

        $selectedElementId = (int)$value['VALUE'];
        $html = '<select name="' . $strHTMLControlName["VALUE"] . '">';
        $html .= '<option value="">(не выбрано)</option>';

        // Выбираем элементы из нужного ИБ
        $rs = \CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            ['IBLOCK_ID' => 2, 'ACTIVE' => 'Y'],
            false,
            false,
            ['ID', 'NAME']
        );

        while ($arElem = $rs->Fetch()) {
            $selected = ($arElem['ID'] == $selectedElementId) ? 'selected' : '';
            $html .= '<option value="'.$arElem['ID'].'" '.$selected.'>'.htmlspecialchars($arElem['NAME']).'</option>';
        }

        $html .= '</select>';
        return $html;
    }
}

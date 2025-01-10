<?php
namespace CustomProperty;

class ProcedureSelector
{
    public static function GetUserTypeDescription()
    {
        return [
            'PROPERTY_TYPE' => 'S', // Тип данных: строка
            'USER_TYPE' => 'procedure_selector', // Уникальный идентификатор
            'DESCRIPTION' => 'Запись на процедуру', // Название в интерфейсе
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'], // Админка: форма редактирования
            'GetAdminListViewHTML' => [__CLASS__, 'GetAdminListViewHTML'], // Админка: вывод в списке
            'GetPublicViewHTML' => [__CLASS__, 'GetPublicViewHTML'], // Публичная часть: отображение
            'GetPublicEditHTML' => [__CLASS__, 'GetPublicEditHTML'], // Публичная часть: редактирование
        ];
    }

    // Админка: вывод поля в форме редактирования
    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        return '<input type="text" name="' . $strHTMLControlName["VALUE"] . '" value="' . htmlspecialchars($value["VALUE"]) . '">';
    }

    // Админка: отображение в списке
    public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
    {
        return htmlspecialchars($value["VALUE"]);
    }

    // Публичная часть: отображение значения
    public static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
    {
        if (!empty($value['VALUE'])) {
            return '<span>' . htmlspecialchars($value['VALUE']) . '</span>';
        }
        return '<span style="color: gray;">Нет данных</span>';
    }

    // Публичная часть: редактирование значения
    public static function GetPublicEditHTML($arProperty, $value, $strHTMLControlName)
    {
        return '<input type="text" name="' . $strHTMLControlName["VALUE"] . '" value="' . htmlspecialchars($value["VALUE"]) . '" placeholder="Введите данные">';
    }
}

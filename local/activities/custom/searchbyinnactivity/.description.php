<?php
use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
    "NAME"        => Loc::getMessage("SEARCHBYINN_DESCR_NAME"),       // Название
    "DESCRIPTION" => Loc::getMessage("SEARCHBYINN_DESCR_DESCR"),      // Описание
    "TYPE"        => "activity",
    "CLASS"       => "CBPSearchByInnActivity", // Имя класса из searchbyinnactivity.php
    "JSCLASS"     => "BizProcActivity",
    "CATEGORY"    => [
        "ID"       => "custom",
        "OWN_ID"   => "custom",
        "OWN_NAME" => "Собственные компоненты",
    ],
    "RETURN" => [
        "Text" => [
            "NAME" => Loc::getMessage("SEARCHBYINN_DESCR_FIELD_TEXT"),
            "TYPE" => "string",
        ],
    ],
];

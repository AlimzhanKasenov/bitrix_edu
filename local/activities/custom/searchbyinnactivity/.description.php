<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
    "NAME"        => Loc::getMessage("SEARCHBYINN_DESCR_NAME"), // Название кубика
    "DESCRIPTION" => Loc::getMessage("SEARCHBYINN_DESCR_DESCR"), // Описание кубика
    "TYPE"        => "activity",
    "CLASS"       => "SearchByInnActivity", // ОБРАТИТЕ ВНИМАНИЕ! Без "CBP" !!!
    "JSCLASS"     => "BizProcActivity",
    "CATEGORY"    => [
        "ID" => "custom",
        "OWN_ID" => "custom",
        "OWN_NAME" => "Пользовательские активности",
    ],
    "RETURN" => [
        "Text" => [
            "NAME" => Loc::getMessage("SEARCHBYINN_DESCR_FIELD_TEXT"),
            "TYPE" => "string",
        ],
        "ZakazchikElementID" => [
            "NAME" => Loc::getMessage("SEARCHBYINN_DESCR_FIELD_ZAKAZCHIK_ELEMENT_ID"),
            "TYPE" => "int",
        ],
    ],
];

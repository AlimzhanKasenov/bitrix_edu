<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Выбор валюты");

// Подключаем компонент
$APPLICATION->IncludeComponent(
    "custom:currency.selector",
    "",
    [
        "CURRENCY" => "EUR", // Указываем валюту по умолчанию
    ]
);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");

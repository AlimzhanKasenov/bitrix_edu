<?php
use Bitrix\Bizproc\FieldType;

$arActivityDescription = [
    'NAME' => 'Получить данные компании по ИНН',
    'DESCRIPTION' => 'Запрашивает данные компании по ИНН через DADATA',
    'TYPE' => 'activity',
    'CLASS' => 'CBPSearchByInnActivity', // Новое имя класса
    'JSCLASS' => 'BizProcActivity',
    'CATEGORY' => [
        'ID' => 'custom',
        'OWN_ID' => 'custom',
        'OWN_NAME' => 'Собственные компоненты',
    ],
    'PROPERTIES' => [
        'INN' => [
            'NAME' => 'ИНН компании',
            'TYPE' => FieldType::STRING,
            'REQUIRED' => true, // Поле обязательно
        ],
    ],
    'RETURN' => [
        'CompanyName' => [
            'NAME' => 'Название компании',
            'TYPE' => FieldType::STRING,
        ],
        'Ogrn' => [
            'NAME' => 'ОГРН',
            'TYPE' => FieldType::STRING,
        ],
        'Kpp' => [
            'NAME' => 'КПП',
            'TYPE' => FieldType::STRING,
        ],
        'Address' => [
            'NAME' => 'Адрес компании',
            'TYPE' => FieldType::STRING,
        ],
    ],
];

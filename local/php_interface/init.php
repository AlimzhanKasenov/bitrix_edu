<?php
use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;

// Подключаем нужные модули
Loader::includeModule('iblock');

$eventManager = EventManager::getInstance();

// Повесим один обработчик на добавление и обновление элемента,
// чтобы при сохранении в любом случае шла "синхронизация".
$eventManager->addEventHandler(
    'iblock',
    'OnBeforeIBlockElementAdd',
    ['MyHandlers', 'syncProcedures']
);

$eventManager->addEventHandler(
    'iblock',
    'OnBeforeIBlockElementUpdate',
    ['MyHandlers', 'syncProcedures']
);

class MyHandlers
{
    /**
     * Синхронизация свойств "Запись на процедуру" -> "Процедуры"
     */
    public static function syncProcedures(&$arFields)
    {
        // Убедитесь, что это нужный инфоблок (врачи).
        // Допустим, у вас ИД инфоблока врачей = 5
        if ((int)$arFields['IBLOCK_ID'] !== 16) {
            return;
        }

        // ВАЖНО: в $arFields["PROPERTY_VALUES"] хранятся значения свойств.
        // Название массива совпадает с символьными кодами свойств или с их ID,
        // в зависимости от настроек инфоблока и ситуации.
        // Допустим, в админке у вас коды "PROTSEDURY" и "ZAPIS_NA_PROTSEDURU".

        // Проверяем, что в текущем сохранении пришло свойство "ZAPIS_NA_PROTSEDURU"
        if (!empty($arFields["PROPERTY_VALUES"]["ZAPIS_NA_PROTSEDURU"])) {
            // Копируем значение из "Запись на процедуру" в "Процедуры"
            $arFields["PROPERTY_VALUES"]["PROTSEDURY"] = $arFields["PROPERTY_VALUES"]["ZAPIS_NA_PROTSEDURU"];
        }
        // Если нужно делать «обратную» копию (с PROTSEDURY в ZAPIS_NA_PROTSEDURU),
        // можно добавить такую же логику. Но обычно делают в одну сторону.
    }
}

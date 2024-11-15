<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");


// Определяем имя файла для логов
define("DEBUG_FILE_NAME", "debug_log.txt");

/**
 * Функция для записи данных в лог
 *
 * @param mixed  $data  Данные для записи
 * @param string $title Заголовок сообщения
 * @return bool
 */
function writeToLog($data, $title = '') {
    if (!defined("DEBUG_FILE_NAME") || !DEBUG_FILE_NAME) {
        return false;
    }

    $log = "\n------------------------\n";
    $log .= date("Y.m.d G:i:s") . "\n";
    $log .= (strlen($title) > 0 ? $title : 'DEBUG') . "\n";
    $log .= "\n------------------------\n";

    $filePath = __DIR__ . "/" . DEBUG_FILE_NAME;
    file_put_contents($filePath, $log, FILE_APPEND);

    return true;
}

// Получение текущей даты и времени
$currentDateTime = date("Y-m-d H:i:s");

// Запись данных в лог с помощью writeToLog
writeToLog($currentDateTime, "Обращение к debug.php");

// Ответ клиенту
echo "Лог успешно записан. Дата и время: " . $currentDateTime;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>

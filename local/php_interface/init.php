<?php
use Bitrix\Main\Loader;

// Автозагрузка классов
Loader::registerAutoLoadClasses(null, [
    'Bitrix\Table\TableTable' => '/local/app/Models/customTable.php',

    'local\file\otus_file_exception_handler_log\OtusFileExceptionHandlerLog' => '/local/file/otus_file_exception_handler_log.php',
]);

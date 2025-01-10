<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Application;

header('Content-Type: application/json; charset=UTF-8');

require $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php";
Loader::includeModule('iblock');

$request = Application::getInstance()->getContext()->getRequest();
$procedureId = (int)$request->getPost('procedureId');
$name       = htmlspecialchars(trim($request->getPost('name')));
$date       = htmlspecialchars(trim($request->getPost('date')));

$iblockId = 22; // ID инфоблока "Бронирование"

global $USER;
if (!$USER->IsAuthorized()) {
    echo json_encode(["status" => "error", "message" => "Вы не авторизованы"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Проверка прав
if (CIBlock::GetPermission($iblockId) < 'W') {
    echo json_encode(["status" => "error", "message" => "Недостаточно прав"], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!$procedureId || !$name || !$date) {
    echo json_encode(["status" => "error", "message" => "Не все поля заполнены"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Проверим занятость времени
if (!isTimeAvailable($iblockId, $date)) {
    echo json_encode(["status" => "error", "message" => "Это время уже занято"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Создаём запись
$el = new \CIBlockElement;
$fields = [
    "IBLOCK_ID" => $iblockId,
    "NAME" => $name,
    "PROPERTY_VALUES" => [
        "PROTSEDURA"    => $procedureId,  // Привязка к элементу
        "VREMYA_ZAPISI" => $date,         // Дата/время
    ],
];

if ($newId = $el->Add($fields)) {
    echo json_encode(["status" => "success", "message" => "Бронирование создано", "id" => $newId], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(["status" => "error", "message" => "Ошибка создания бронирования: ".$el->LAST_ERROR], JSON_UNESCAPED_UNICODE);
}

// --- Функция для проверки занятости времени ---
function isTimeAvailable($iblockId, $date)
{
    $res = \CIBlockElement::GetList([], ["IBLOCK_ID"=>$iblockId, "PROPERTY_VREMYA_ZAPISI"=>$date], false, false, ["ID"]);
    return !$res->Fetch();
}

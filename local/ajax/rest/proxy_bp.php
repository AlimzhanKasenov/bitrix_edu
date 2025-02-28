<?php
require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";

use Bitrix\Main\Application;

/**
 * Транслитерация строки для создания ELEMENT_CODE
 *
 * @param string $str Исходная строка
 * @return string Транслитерированная строка
 */
function translit($str) {
    $rus = ['а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я'];
    $lat = ['a', 'b', 'v', 'g', 'd', 'e', 'e', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'ts', 'ch', 'sh', 'shch', '', 'y', '', 'e', 'yu', 'ya'];
    $str = str_replace($rus, $lat, mb_strtolower($str));
    $str = preg_replace('/[^a-z0-9]+/u', '-', $str);
    return trim($str, '-');
}

$request = Application::getInstance()->getContext()->getRequest();
$data = json_decode($request->getInput(), true);

$logFile = $_SERVER["DOCUMENT_ROOT"] . "/local/logs/bp.log";
file_put_contents($logFile, date("Y-m-d H:i:s") . " - Входящие данные: " . print_r($data, true) . "\n", FILE_APPEND);

if (!isset($data['action'])) {
    file_put_contents($logFile, "Ошибка: не передано действие\n", FILE_APPEND);
    echo json_encode(["status" => "error", "message" => "Не передано действие"]);
    exit;
}

$webhookUrl = "https://co45937.tw1.ru/rest/1/jt7cu0zvractjbmr/";
$listId = 29;

switch ($data['action']) {
    case "create":
        $method = "lists.element.add";
        $elementCode = translit($data['NAME']) . "_" . time();

        $queryData = [
            'IBLOCK_TYPE_ID' => 'bitrix_processes',
            'IBLOCK_ID' => $listId,
            'ELEMENT_CODE' => $elementCode,
            'FIELDS' => [
                'NAME' => $data['NAME'],
                'PROPERTY_88' => $data['DESCRIPTION'] ?? '',
                'PROPERTY_89' => $data['STATUS'] ?? 'Новый',
                'PROPERTY_90' => [$data['USER_ID'] ?? "1"],
                'PROPERTY_91' => date("Y-m-d H:i:s"),
            ]
        ];
        break;

    case "read":
        $method = "lists.element.get";
        $queryData = [
            'IBLOCK_TYPE_ID' => 'bitrix_processes',
            'IBLOCK_ID' => $listId,
            'ELEMENT_ID' => $data['ID']
        ];
        break;

    case "update":
        $method = "lists.element.update";
        $queryData = [
            'IBLOCK_TYPE_ID' => 'bitrix_processes',
            'IBLOCK_ID' => $listId,
            'ELEMENT_ID' => $data['ID'],
            'FIELDS' => [
                'NAME' => $data['NAME'],
                'PROPERTY_88' => $data['DESCRIPTION'] ?? '',
                'PROPERTY_89' => $data['STATUS'] ?? '',
            ]
        ];
        break;

    case "delete":
        $method = "lists.element.delete";
        $queryData = [
            'IBLOCK_TYPE_ID' => 'bitrix_processes',
            'IBLOCK_ID' => $listId,
            'ELEMENT_ID' => $data['ID']
        ];
        break;

    default:
        file_put_contents($logFile, "Ошибка: неверное действие ({$data['action']})\n", FILE_APPEND);
        echo json_encode(["status" => "error", "message" => "Неверное действие"]);
        exit;
}

file_put_contents($logFile, "Отправляем в Bitrix24: " . print_r($queryData, true) . "\n", FILE_APPEND);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhookUrl . $method);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($queryData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

file_put_contents($logFile, "Ответ Bitrix24 ({$httpCode}): " . $response . "\n", FILE_APPEND);

echo $response;

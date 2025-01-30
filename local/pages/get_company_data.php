<?php
// Подключаем Битрикс (если планируется взаимодействие с его API)
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Application;

// Устанавливаем заголовки ответа
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Проверяем, что запрос пришёл методом POST
$request = Application::getInstance()->getContext()->getRequest();

if (!$request->isPost()) {
    echo json_encode(["error" => "Метод запроса должен быть POST"]);
    exit;
}

// Получаем ИНН из запроса
$inn = trim($request->getPost("inn"));

if (!$inn || !preg_match('/^\d{10,12}$/', $inn)) {
    echo json_encode(["error" => "Некорректный ИНН"]);
    exit;
}

// Ключи Dadata API
$apiKey = "e035caa2775243da49a0701a724b7dae11ee400f";
$secretKey = "9d2658677fc6f36055702f21e640bdd8c0620204";

// URL API Dadata для поиска компании по ИНН
$url = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/findById/party";

// Формируем данные для запроса
$data = [
    "query" => $inn,
    "count" => 1
];

// Отправляем запрос в Dadata
$response = sendPostRequest($url, $data, $apiKey);

// Проверяем, получены ли данные
if (!$response || empty($response["suggestions"])) {
    echo json_encode(["error" => "Компания не найдена"]);
    exit;
}

// Получаем данные компании
$company = $response["suggestions"][0];

// Формируем ответ
$result = [
    "name"    => $company["value"] ?? "Неизвестная компания",
    "ogrn"    => $company["data"]["ogrn"] ?? "",
    "kpp"     => $company["data"]["kpp"] ?? "",
    "address" => $company["data"]["address"]["unrestricted_value"] ?? ""
];

// Отправляем JSON-ответ
echo json_encode($result, JSON_UNESCAPED_UNICODE);

/**
 * Функция для отправки POST-запроса с авторизацией по API-ключу
 *
 * @param string $url  URL API
 * @param array  $data Данные для отправки
 * @param string $apiKey API-ключ Dadata
 *
 * @return array|null Ответ в формате массива
 */
function sendPostRequest($url, $data, $apiKey)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Accept: application/json",
        "Authorization: Token " . $apiKey
    ]);

    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
}

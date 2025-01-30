<?php
use Bitrix\Main\Loader;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class CBPSearchByInnActivity extends CBPActivity
{
    public function __construct($name)
    {
        parent::__construct($name);

        $this->arProperties = [
            "INN" => null,  // Входной параметр
            "CompanyName" => null,  // Выходные параметры
            "Ogrn" => null,
            "Kpp" => null,
            "Address" => null,
        ];
    }

    public function Execute()
    {
        if (!Loader::includeModule("bizproc")) {
            $this->WriteToTrackingService("Модуль бизнес-процессов не загружен.", 0, CBPTrackingType::Error);
            return CBPActivityExecutionStatus::Closed;
        }

        $inn = $this->INN;

        if (!$inn) {
            $this->WriteToTrackingService("ИНН не указан.", 0, CBPTrackingType::Error);
            return CBPActivityExecutionStatus::Closed;
        }

        $url = "/local/pages/get_company_data.php";
        $response = $this->sendPostRequest($url, ["inn" => $inn]);

        if (!$response || isset($response["error"])) {
            $this->WriteToTrackingService("Ошибка при получении данных: " . ($response["error"] ?? "Неизвестная ошибка"), 0, CBPTrackingType::Error);
            return CBPActivityExecutionStatus::Closed;
        }

        $this->CompanyName = $response["name"] ?? "";
        $this->Ogrn = $response["ogrn"] ?? "";
        $this->Kpp = $response["kpp"] ?? "";
        $this->Address = $response["address"] ?? "";

        return CBPActivityExecutionStatus::Closed;
    }

    private function sendPostRequest($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/x-www-form-urlencoded"]);

        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }
}

<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Bizproc\Activity\BaseActivity;
use Bitrix\Bizproc\FieldType;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use CIBlockElement;
use Bitrix\Bizproc\Activity\PropertiesDialog;

class CBPSearchByInnActivity extends BaseActivity
{

    public function __construct($name)
    {
        parent::__construct($name);

        $this->arProperties = [
            'Inn'                 => '',
            'Text'                => null,
            'ZakazchikElementID'  => null,
            'ElementId26'         => null, // <-- добавили
        ];

        $this->SetPropertiesTypes([
            'Text'               => ['Type' => FieldType::STRING],
            'ZakazchikElementID' => ['Type' => FieldType::INT],
            'ElementId26'        => ['Type' => FieldType::INT], // <-- добавили
        ]);
    }

    protected static function getFileName(): string
    {
        return __FILE__;
    }

    /**
     * Основная логика выполнения кубика
     */
    protected function internalExecute(): ErrorCollection
    {
        $errors = parent::internalExecute();

        // Проверка подключения модуля инфоблоков
        if (!Loader::includeModule('iblock')) {
            $errors->setError(new \Bitrix\Main\Error('Модуль iblock не установлен'));
            return $errors;
        }

        // Проверка входных данных
        $inn = trim($this->Inn);
        if (!$inn || !preg_match('/^\d{10,12}$/', $inn)) {
            $errors->setError(new \Bitrix\Main\Error('Некорректный ИНН'));
            return $errors;
        }

        // Настройки инфоблока
        $iblockId27 = 27; // Инфоблок для компаний

        // Ищем компанию в Dadata
        $token = "e035caa2775243da49a0701a724b7dae11ee400f"; // Укажите реальный API-ключ
        $url = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/findById/party";

        $companyData = $this->getCompanyFromDadata($url, $inn, $token);

        if (!$companyData) {
            $errors->setError(new \Bitrix\Main\Error('Компания по ИНН не найдена'));
            return $errors;
        }

        // Создаём элемент в инфоблоке №27
        $el = new CIBlockElement();
        $fields = [
            "IBLOCK_ID" => $iblockId27,
            "NAME" => $companyData["name"],
            "ACTIVE" => "Y",
            "PROPERTY_VALUES" => [
                "INN" => $companyData["inn"] ?? "",
                "KPP" => $companyData["kpp"] ?? "",
                "OGRN" => $companyData["ogrn"] ?? "",
                "ADRES" => $companyData["address"] ?? "",
            ],
        ];

        $elementId = $el->Add($fields);

        if (!$elementId) {
            $errors->setError(new \Bitrix\Main\Error('Ошибка создания элемента: ' . $el->LAST_ERROR));
            return $errors;
        }

        // Устанавливаем выходные параметры
        $this->preparedProperties['Text'] = $companyData["name"];
        $this->preparedProperties['ZakazchikElementID'] = $elementId;

        // <--- ДОПОЛНИТЕЛЬНО: записываем этот ID в элемент ИБ 26
        $elementId26 = (int)$this->ElementId26;
        if ($elementId26 > 0)
        {
            CIBlockElement::SetPropertyValuesEx($elementId26, 26, ["ZAKAZCHIK" => $elementId]);
            $this->log("ID элемента 27: {$elementId} записан в ZAKAZCHIK для элемента 26: {$elementId26}");
        }

        $this->log("Компания: {$companyData['name']}, ID элемента (ИБ27): {$elementId}");

        return $errors;
    }


    /**
     * Запрос к Dadata
     */

    private function getCompanyFromDadata($url, $inn, $token)
    {
        $data = [
            "query" => $inn,
            "count" => 1,
            "type" => "LEGAL",
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Token " . $token,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if (empty($result['suggestions'])) {
            return null;
        }

        $company = $result['suggestions'][0];
        return [
            "name"    => $company["value"] ?? "Неизвестная компания",
            "inn"     => $company["data"]["inn"] ?? "",
            "ogrn"    => $company["data"]["ogrn"] ?? "",
            "kpp"     => $company["data"]["kpp"] ?? "",
            "address" => $company["data"]["address"]["unrestricted_value"] ?? "",
        ];
    }

    /**
     * Настройка полей кубика
     */

public static function getPropertiesDialogMap(?PropertiesDialog $dialog = null): array
    {
        return [
            'Inn' => [
                'Name'      => Loc::getMessage('SEARCHBYINN_ACTIVITY_FIELD_INN'),
                'FieldName' => 'inn',
                'Type'      => FieldType::STRING,
                'Required'  => true,
            ],
            'ElementId26' => [
                'Name'      => Loc::getMessage('SEARCHBYINN_ACTIVITY_FIELD_ELEMENT_ID_26'),
                'FieldName' => 'element_id_26',
                'Type'      => FieldType::INT,
                'Required'  => false,
            ],
        ];
    }

}

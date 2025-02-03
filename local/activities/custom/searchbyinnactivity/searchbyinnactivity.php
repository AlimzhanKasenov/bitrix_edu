<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Bizproc\Activity\BaseActivity;
use Bitrix\Bizproc\Activity\PropertiesDialog;
use Bitrix\Bizproc\FieldType;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use CIBlockElement;

class CBPSearchByInnActivity extends BaseActivity
{
    public function __construct($name)
    {
        parent::__construct($name);

        $this->arProperties = [
            'Inn'                => '',    // Вход: ИНН
            'Text'               => null,  // Выход: название компании
            'ZakazchikElementID' => null,  // Выход: ID найденного/созданного элемента в ИБ 27
            'ElementId26'        => null,  // Вход: ID элемента в ИБ 26, куда пишем ZAKAZCHIK
        ];

        $this->SetPropertiesTypes([
            'Text'               => ['Type' => FieldType::STRING],
            'ZakazchikElementID' => ['Type' => FieldType::INT],
            'ElementId26'        => ['Type' => FieldType::INT],
        ]);
    }

    // Требуется в некоторых версиях Битрикс для корректной работы
    protected static function getFileName(): string
    {
        return __FILE__;
    }

    /**
     * Основная логика выполнения активности
     */
    protected function internalExecute(): ErrorCollection
    {
        // Коллекция ошибок
        $errors = parent::internalExecute();

        // 1. Проверяем модуль инфоблоков
        if (!Loader::includeModule('iblock')) {
            $errors->setError(new Error('Модуль iblock не установлен'));
            return $errors;
        }

        // 2. Проверка входных данных — ИНН
        $inn = trim($this->Inn);
        if (!$inn || !preg_match('/^\d{10,12}$/', $inn)) {
            $errors->setError(new Error('Некорректный ИНН'));
            return $errors;
        }

        // Параметры
        $iblockId27 = 27; // Инфоблок «Компании»
        $iblockId26 = 26; // Инфоблок, где будем писать ZAKAZCHIK
        $zakazchikPropCode = "ZAKAZCHIK"; // Код свойства в ИБ 26

        // 3. Сначала проверяем, может компания уже есть в ИБ 27
        $res = CIBlockElement::GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID'     => $iblockId27,
                '=PROPERTY_INN' => $inn,
                'ACTIVE'        => 'Y',
            ],
            false,
            ['nTopCount' => 1],
            ['ID', 'NAME']
        );

        $existingElement = $res->Fetch();

        if ($existingElement) {
            // Если уже есть
            $elementId   = (int)$existingElement['ID'];
            $companyName = $existingElement['NAME'];

            // Запишем лог (опционально)
            $this->log("Найдена существующая компания: {$companyName} (ID: {$elementId})");
        } else {
            // 4. Если компания не найдена, обращаемся к DaData
            $token = "e035caa2775243da49a0701a724b7dae11ee400f"; // замените на реальный ключ
            $url   = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/findById/party";

            $companyData = $this->getCompanyFromDadata($url, $inn, $token);
            if (!$companyData) {
                $errors->setError(new Error('Компания по ИНН не найдена в DaData'));
                return $errors;
            }

            // 5. Создаём новую запись в ИБ 27
            $el = new CIBlockElement();
            $fields = [
                "IBLOCK_ID" => $iblockId27,
                "NAME"      => $companyData["name"],
                "ACTIVE"    => "Y",
                "PROPERTY_VALUES" => [
                    "INN"   => $companyData["inn"] ?? "",
                    "KPP"   => $companyData["kpp"] ?? "",
                    "OGRN"  => $companyData["ogrn"] ?? "",
                    "ADRES" => $companyData["address"] ?? "",
                ],
            ];

            $elementId = $el->Add($fields);
            if (!$elementId) {
                $errors->setError(new Error('Ошибка создания элемента: ' . $el->LAST_ERROR));
                return $errors;
            }

            // Запомним название
            $companyName = $companyData["name"];

            // Запишем лог (опционально)
            $this->log("Создана новая компания: {$companyName} (ID: {$elementId})");
        }

        // 6. Привязываем (записываем) эту компанию в элемент ИБ 26 (если указан ElementId26)
        $elementId26 = (int)$this->ElementId26;
        if ($elementId26 > 0) {
            CIBlockElement::SetPropertyValuesEx(
                $elementId26,
                $iblockId26,
                [$zakazchikPropCode => $elementId]
            );

            $this->log("ID компании: {$elementId} записан в свойство ZAKAZCHIK у элемента ИБ 26: {$elementId26}");
        }

        // 7. Устанавливаем выходные параметры
        //    (будут доступны дальше в БП как переменные Text, ZakazchikElementID)
        $this->preparedProperties['Text']               = $companyName;
        $this->preparedProperties['ZakazchikElementID'] = $elementId;

        return $errors;
    }

    /**
     * Запрос к DaData
     */
    private function getCompanyFromDadata($url, $inn, $token)
    {
        $data = [
            "query" => $inn,
            "count" => 1,
            "type"  => "LEGAL",
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST,           true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,     json_encode($data));
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
     * Карта полей для настроек активности в Дизайнере БП
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

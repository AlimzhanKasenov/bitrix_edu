<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Bizproc\Activity\BaseActivity;
use Bitrix\Bizproc\FieldType;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc\Activity\PropertiesDialog;
use Bitrix\Main\Loader;

class CBPSearchByInnActivity extends BaseActivity
{
    public function __construct($name)
    {
        parent::__construct($name);

        $this->arProperties = [
            'Inn'  => '',   // Входное значение (ИНН)
            'Text' => null, // Выходной результат (название компании)
            // Новое свойство для ID созданного элемента
            'ZakazchikElementID' => null,
        ];

        // Указываем типы возвращаемых свойств
        $this->SetPropertiesTypes([
            'Text' => ['Type' => FieldType::STRING],
            'ZakazchikElementID' => ['Type' => FieldType::INT],
        ]);
    }

    protected static function getFileName(): string
    {
        return __FILE__;
    }

    /**
     * Основная логика выполнения
     */
    protected function internalExecute(): ErrorCollection
    {
        $errors = parent::internalExecute();

        // Загружаем модуль iblock (для CIBlockElement)
        if (!Loader::includeModule('iblock'))
        {
            $errors->setError(new \Bitrix\Main\Error('Модуль iblock не установлен'));
            return $errors;
        }

        // Пример использования Dadata
        $token  = "ТОКЕН";
        $secret = "СЕКРЕТ";
        // Предполагаем, что класс Dadata загружен через автозагрузчик
        $dadata = new Dadata($token, $secret);
        $dadata->init();

        $fields = ["query" => $this->Inn, "count" => 5];
        $response = $dadata->suggest("party", $fields);

        $companyName = 'Компания не найдена!';
        $elemId = 0;

        if (!empty($response['suggestions']))
        {
            // Берём первую подсказку
            $firstSuggest = $response['suggestions'][0];
            $companyName  = $firstSuggest['value']; // Название организации
            $data         = $firstSuggest['data'];  // Массив с остальными полями

            // Создаём элемент в инфоблоке №27
            $el = new \CIBlockElement();

            $arLoad = [
                "IBLOCK_ID" => 27,            // Ваш инфоблок для "Заказчика"
                "NAME"      => $companyName,  // Поле NAME (обязательное)
                "ACTIVE"    => "Y",
                "PROPERTY_VALUES" => [
                    "INN"  => $data["inn"]  ?? "",
                    "KPP"  => $data["kpp"]  ?? "",
                    "OGRN" => $data["ogrn"] ?? "",
                    // Адрес чаще всего лежит здесь
                    "ADRES" => $data["address"]["unrestricted_value"] ?? "",
                ],
            ];

            $elemId = $el->Add($arLoad);
            if (!$elemId)
            {
                // Если элемент не создался, запишем ошибку
                $errors->setError(new \Bitrix\Main\Error("Не удалось создать элемент: ".$el->LAST_ERROR));
            }
        }

        // Записываем результат в поля активности
        $this->preparedProperties['Text']                = $companyName;
        $this->preparedProperties['ZakazchikElementID']  = $elemId;

        // Лог для отладки
        $this->log("Найдено: " . $companyName . ", ID элемента = " . $elemId);

        return $errors;
    }

    /**
     * Описание полей, которые пользователь видит при настройке активности
     */
    public static function getPropertiesDialogMap(?PropertiesDialog $dialog = null): array
    {
        return [
            'Inn' => [
                'Name'      => Loc::getMessage('SEARCHBYINN_ACTIVITY_FIELD_SUBJECT'),
                'FieldName' => 'inn',
                'Type'      => FieldType::STRING,
                'Required'  => true,
            ],
        ];
    }
}

<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Bizproc\Activity\BaseActivity;
use Bitrix\Bizproc\FieldType;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc\Activity\PropertiesDialog;

class CBPSearchByInnActivity extends BaseActivity
{
    public function __construct($name)
    {
        parent::__construct($name);

        $this->arProperties = [
            'Inn'  => '',   // Входное значение (ИНН)
            'Text' => null, // Выходной результат
        ];

        // Указываем тип поля, чтобы движок понимал, что это строка
        $this->SetPropertiesTypes([
            'Text' => ['Type' => FieldType::STRING],
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

        // Пример использования Dadata
        $token  = "ТОКЕН";
        $secret = "СЕКРЕТ";
        // Предполагаем, что класс Dadata загружен где-то до этого или autoload
        $dadata = new Dadata($token, $secret);
        $dadata->init();

        $fields = ["query" => $this->Inn, "count" => 5];
        $response = $dadata->suggest("party", $fields);

        $companyName = 'Компания не найдена!';
        if (!empty($response['suggestions'])) {
            $companyName = $response['suggestions'][0]['value'];
        }

        // Записываем результат (станет доступен в "RETURN" => "Text")
        $this->preparedProperties['Text'] = $companyName;

        // Лог для отладки (не обязателен)
        $this->log("Найдено: " . $companyName);

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

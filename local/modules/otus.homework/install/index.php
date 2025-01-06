<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Main\EventManager;

Loc::loadMessages(__FILE__);

class otus_homework extends CModule
{
    public $MODULE_ID = 'otus.homework';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;

    /**
     * Конструктор модуля. Подтягивает версию, дату и другое из version.php
     */
    function __construct()
    {
        $arModuleVersion = array();
        include(__DIR__ . '/version.php');
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('OTUS_VACATION_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('OTUS_VACATION_MODULE_DESC');

        $this->PARTNER_NAME = Loc::getMessage('OTUS_VACATION_PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('OTUS_VACATION_PARTNER_URI');
    }

    /**
     * Проверяем, что версия главного модуля (bitrix) не ниже 20.00.00
     * (то есть модуль поддерживает D7)
     */
    public function isVersionD7()
    {
        return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '20.00.00');
    }

    /**
     * Возвращает путь к папке модуля
     */
    public function GetPath($notDocumentRoot = false)
    {
        if ($notDocumentRoot) {
            return str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
        } else {
            return dirname(__DIR__);
        }
    }

    /**
     * Установка модуля
     */
    public function DoInstall()
    {
        global $APPLICATION;

        if ($this->isVersionD7()) {
            // Регистрируем модуль в системе
            \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);

            // Устанавливаем базу (создаём инфоблок и т.п.)
            $this->InstallDB();
            // Копируем компоненты в /bitrix/components
            $this->installFiles();
            // Регистрируем обработчики событий
            $this->InstallEvents();
        } else {
            $APPLICATION->ThrowException(Loc::getMessage('OTUS_VACATION_INSTALL_ERROR_VERSION'));
        }
    }

    /**
     * Удаление модуля
     */
    public function DoUninstall()
    {
        // Удаляем всё, что создали
        $this->UnInstallDB();
        $this->UnInstallEvents();

        // Снимаем регистрацию модуля
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    /**
     * Копирование установочных файлов (компонентов) в систему
     */
    public function installFiles($arParams = array())
    {
        $component_path = $this->GetPath() . '/install/components';

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($component_path)) {
            CopyDirFiles($component_path, $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components', true, true);
        } else {
            throw new \Bitrix\Main\IO\InvalidPathException($component_path);
        }
    }

    /**
     * Установка БД: создаём инфоблок (пример) и запоминаем его ID в настройках модуля
     */
    public function InstallDB()
    {
        \Bitrix\Main\Loader::includeModule('iblock');
        $arAccess = [
            1 => 'X', // Группа 'Администраторы' — полный доступ
            2 => 'R', // Группа 'Все пользователи (в т.ч. неавторизованные)' — чтение
        ];

        // Параметры создаваемого инфоблока
        $arFields = Array(
            'ACTIVE' => 'Y',
            'NAME' => 'Тестовый универсальный список',
            'CODE' => 'test_list',
            'IBLOCK_TYPE_ID' => 'lists',
            'SITE_ID' => 's1',
            'SORT' => '500',
            'GROUP_ID' => $arAccess, // Права доступа
            'FIELDS' => array(
                // Настройки полей инфоблока (картинки, символьные коды и т.д.)
                'DETAIL_PICTURE' => array(
                    'IS_REQUIRED' => 'N',
                    'DEFAULT_VALUE' => array(
                        'SCALE' => 'Y',
                        'WIDTH' => '600',
                        'HEIGHT' => '600',
                        'IGNORE_ERRORS' => 'Y',
                        'METHOD' => 'resample',
                        'COMPRESSION' => '95',
                    ),
                ),
                'PREVIEW_PICTURE' => array(
                    'IS_REQUIRED' => 'N',
                    'DEFAULT_VALUE' => array(
                        'SCALE' => 'Y',
                        'WIDTH' => '140',
                        'HEIGHT' => '140',
                        'IGNORE_ERRORS' => 'Y',
                        'METHOD' => 'resample',
                        'COMPRESSION' => '95',
                        'FROM_DETAIL' => 'Y',
                        'DELETE_WITH_DETAIL' => 'Y',
                        'UPDATE_WITH_DETAIL' => 'Y',
                    ),
                ),
                'SECTION_PICTURE' => array(
                    'IS_REQUIRED' => 'N',
                    'DEFAULT_VALUE' => array(
                        'SCALE' => 'Y',
                        'WIDTH' => '235',
                        'HEIGHT' => '235',
                        'IGNORE_ERRORS' => 'Y',
                        'METHOD' => 'resample',
                        'COMPRESSION' => '95',
                        'FROM_DETAIL' => 'Y',
                        'DELETE_WITH_DETAIL' => 'Y',
                        'UPDATE_WITH_DETAIL' => 'Y',
                    ),
                ),
                'CODE' => array(
                    'IS_REQUIRED' => 'Y',
                    'DEFAULT_VALUE' => array(
                        'UNIQUE' => 'Y',
                        'TRANSLITERATION' => 'Y',
                        'TRANS_LEN' => '30',
                        'TRANS_CASE' => 'L',
                        'TRANS_SPACE' => '-',
                        'TRANS_OTHER' => '-',
                        'TRANS_EAT' => 'Y',
                        'USE_GOOGLE' => 'N',
                    ),
                ),
                'SECTION_CODE' => array(
                    'IS_REQUIRED' => 'Y',
                    'DEFAULT_VALUE' => array(
                        'UNIQUE' => 'Y',
                        'TRANSLITERATION' => 'Y',
                        'TRANS_LEN' => '30',
                        'TRANS_CASE' => 'L',
                        'TRANS_SPACE' => '-',
                        'TRANS_OTHER' => '-',
                        'TRANS_EAT' => 'Y',
                        'USE_GOOGLE' => 'N',
                    ),
                ),
                'DETAIL_TEXT_TYPE' => array(
                    'DEFAULT_VALUE' => 'html',
                ),
                'SECTION_DESCRIPTION_TYPE' => array(
                    'DEFAULT_VALUE' => 'html',
                ),
                'IBLOCK_SECTION' => array(
                    'IS_REQUIRED' => 'Y',
                ),
                // Логирование изменений
                'LOG_SECTION_ADD' => array('IS_REQUIRED' => 'Y'),
                'LOG_SECTION_EDIT' => array('IS_REQUIRED' => 'Y'),
                'LOG_SECTION_DELETE' => array('IS_REQUIRED' => 'Y'),
                'LOG_ELEMENT_ADD' => array('IS_REQUIRED' => 'Y'),
                'LOG_ELEMENT_EDIT' => array('IS_REQUIRED' => 'Y'),
                'LOG_ELEMENT_DELETE' => array('IS_REQUIRED' => 'Y'),
            ),
            'INDEX_SECTION' => 'Y',
            'INDEX_ELEMENT' => 'Y',
            'VERSION' => 2,
        );

        // Создаём сам инфоблок
        $ib = new \CIBlock;
        $iblockId = $ib->Add($arFields);

        // Сохраняем его ID в настройках модуля (чтобы потом удобно получить)
        \Bitrix\Main\Config\Option::set($this->MODULE_ID, 'test_iblock_id', $iblockId);
    }

    /**
     * Удаление БД: удаляем ранее созданный инфоблок
     */
    public function UnInstallDB()
    {
        $ib = new \CIBlock;
        $iblockId = \Bitrix\Main\Config\Option::get($this->MODULE_ID, 'test_iblock_id');
        $ib->Delete($iblockId);
    }

    /**
     * Установка (регистрация) обработчиков событий
     */
    public function InstallEvents()
    {
        $eventManager = EventManager::getInstance();

        // Регистрируем наш обработчик, который добавляет вкладку в CRM
        $eventManager->registerEventHandler(
            'crm',
            'onEntityDetailsTabsInitialized',
            $this->MODULE_ID,
            '\\Otus\\Homework\\Crm\\Handlers',
            'updateTabs'
        );

        return true;
    }

    /**
     * Удаление (отмена регистрации) обработчиков событий
     */
    public function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();

        $eventManager->unRegisterEventHandler(
            'crm',
            'onEntityDetailsTabsInitialized',
            $this->MODULE_ID,
            '\\Otus\\Vacation\\Crm\\Handlers',
            'updateTabs'
        );

        return true;
    }
}

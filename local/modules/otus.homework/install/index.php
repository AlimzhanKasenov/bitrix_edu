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

    public function isVersionD7()
    {
        return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '20.00.00');
    }

    public function GetPath($notDocumentRoot = false)
    {
        if ($notDocumentRoot) {
            return str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
        } else {
            return dirname(__DIR__);
        }
    }

    public function DoInstall()
    {
        global $APPLICATION;

        if ($this->isVersionD7()) {
            \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);

            $this->InstallDB();
            $this->installFiles();
            $this->InstallEvents();
        } else {
            $APPLICATION->ThrowException(Loc::getMessage('OTUS_VACATION_INSTALL_ERROR_VERSION'));
        }
    }

    public function DoUninstall()
    {
        $this->UnInstallDB();
        $this->UnInstallEvents();

        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    public function installFiles($arParams = array())
    {
        $component_path = $this->GetPath() . '/install/components';

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($component_path)) {
            CopyDirFiles($component_path, $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components', true, true);
        } else {
            throw new \Bitrix\Main\IO\InvalidPathException($component_path);
        }
    }

    public function InstallDB()
    {
        \Bitrix\Main\Loader::includeModule('iblock');
        $arAccess = [
            1 => 'X',
            2 => 'R',
        ];
        $arFields = Array(
            'ACTIVE' => 'Y',
            'NAME' => 'Тестовый универсальный список',
            'CODE' => 'test_list',
            'IBLOCK_TYPE_ID' => 'lists',
            'SITE_ID' => 's1',
            'SORT' => '500',
            'GROUP_ID' => $arAccess, // Права доступа
            'FIELDS' => array(
                'DETAIL_PICTURE' => array(
                    'IS_REQUIRED' => 'N', // не обязательное
                    'DEFAULT_VALUE' => array(
                        'SCALE' => 'Y', // возможные значения: Y|N. Если равно 'Y', то изображение будет отмасштабировано.
                        'WIDTH' => '600', // целое число. Размер картинки будет изменен таким образом, что ее ширина не будет превышать значения этого поля.
                        'HEIGHT' => '600', // целое число. Размер картинки будет изменен таким образом, что ее высота не будет превышать значения этого поля.
                        'IGNORE_ERRORS' => 'Y', // возможные значения: Y|N. Если во время изменения размера картинки были ошибки, то при значении 'N' будет сгенерирована ошибка.
                        'METHOD' => 'resample', // возможные значения: resample или пусто. Значение поля равное 'resample' приведет к использованию функции масштабирования imagecopyresampled, а не imagecopyresized. Это более качественный метод, но требует больше серверных ресурсов.
                        'COMPRESSION' => '95', // целое от 0 до 100. Если значение больше 0, то для изображений jpeg оно будет использовано как параметр компрессии. 100 соответствует наилучшему качеству при большем размере файла.
                    ),
                ),
                'PREVIEW_PICTURE' => array(
                    'IS_REQUIRED' => 'N', // не обязательное
                    'DEFAULT_VALUE' => array(
                        'SCALE' => 'Y', // возможные значения: Y|N. Если равно 'Y', то изображение будет отмасштабировано.
                        'WIDTH' => '140', // целое число. Размер картинки будет изменен таким образом, что ее ширина не будет превышать значения этого поля.
                        'HEIGHT' => '140', // целое число. Размер картинки будет изменен таким образом, что ее высота не будет превышать значения этого поля.
                        'IGNORE_ERRORS' => 'Y', // возможные значения: Y|N. Если во время изменения размера картинки были ошибки, то при значении 'N' будет сгенерирована ошибка.
                        'METHOD' => 'resample', // возможные значения: resample или пусто. Значение поля равное 'resample' приведет к использованию функции масштабирования imagecopyresampled, а не imagecopyresized. Это более качественный метод, но требует больше серверных ресурсов.
                        'COMPRESSION' => '95', // целое от 0 до 100. Если значение больше 0, то для изображений jpeg оно будет использовано как параметр компрессии. 100 соответствует наилучшему качеству при большем размере файла.
                        'FROM_DETAIL' => 'Y', // возможные значения: Y|N. Указывает на необходимость генерации картинки предварительного просмотра из детальной.
                        'DELETE_WITH_DETAIL' => 'Y', // возможные значения: Y|N. Указывает на необходимость удаления картинки предварительного просмотра при удалении детальной.
                        'UPDATE_WITH_DETAIL' => 'Y', // возможные значения: Y|N. Указывает на необходимость обновления картинки предварительного просмотра при изменении детальной.
                    ),
                ),
                'SECTION_PICTURE' => array(
                    'IS_REQUIRED' => 'N', // не обязательное
                    'DEFAULT_VALUE' => array(
                        'SCALE' => 'Y', // возможные значения: Y|N. Если равно 'Y', то изображение будет отмасштабировано.
                        'WIDTH' => '235', // целое число. Размер картинки будет изменен таким образом, что ее ширина не будет превышать значения этого поля.
                        'HEIGHT' => '235', // целое число. Размер картинки будет изменен таким образом, что ее высота не будет превышать значения этого поля.
                        'IGNORE_ERRORS' => 'Y', // возможные значения: Y|N. Если во время изменения размера картинки были ошибки, то при значении 'N' будет сгенерирована ошибка.
                        'METHOD' => 'resample', // возможные значения: resample или пусто. Значение поля равное 'resample' приведет к использованию функции масштабирования imagecopyresampled, а не imagecopyresized. Это более качественный метод, но требует больше серверных ресурсов.
                        'COMPRESSION' => '95', // целое от 0 до 100. Если значение больше 0, то для изображений jpeg оно будет использовано как параметр компрессии. 100 соответствует наилучшему качеству при большем размере файла.
                        'FROM_DETAIL' => 'Y', // возможные значения: Y|N. Указывает на необходимость генерации картинки предварительного просмотра из детальной.
                        'DELETE_WITH_DETAIL' => 'Y', // возможные значения: Y|N. Указывает на необходимость удаления картинки предварительного просмотра при удалении детальной.
                        'UPDATE_WITH_DETAIL' => 'Y', // возможные значения: Y|N. Указывает на необходимость обновления картинки предварительного просмотра при изменении детальной.
                    ),
                ),
                // Символьный код элементов
                'CODE' => array(
                    'IS_REQUIRED' => 'Y', // Обязательное
                    'DEFAULT_VALUE' => array(
                        'UNIQUE' => 'Y', // Проверять на уникальность
                        'TRANSLITERATION' => 'Y', // Транслитерировать
                        'TRANS_LEN' => '30', // Максмальная длина транслитерации
                        'TRANS_CASE' => 'L', // Приводить к нижнему регистру
                        'TRANS_SPACE' => '-', // Символы для замены
                        'TRANS_OTHER' => '-',
                        'TRANS_EAT' => 'Y',
                        'USE_GOOGLE' => 'N',
                    ),
                ),
                // Символьный код разделов
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
                'DETAIL_TEXT_TYPE' => array(      // Тип детального описания
                    'DEFAULT_VALUE' => 'html',
                ),
                'SECTION_DESCRIPTION_TYPE' => array(
                    'DEFAULT_VALUE' => 'html',
                ),
                'IBLOCK_SECTION' => array(         // Привязка к разделам обязательноа
                    'IS_REQUIRED' => 'Y',
                ),
                'LOG_SECTION_ADD' => array('IS_REQUIRED' => 'Y'), // Журналирование
                'LOG_SECTION_EDIT' => array('IS_REQUIRED' => 'Y'),
                'LOG_SECTION_DELETE' => array('IS_REQUIRED' => 'Y'),
                'LOG_ELEMENT_ADD' => array('IS_REQUIRED' => 'Y'),
                'LOG_ELEMENT_EDIT' => array('IS_REQUIRED' => 'Y'),
                'LOG_ELEMENT_DELETE' => array('IS_REQUIRED' => 'Y'),
            ),
            'INDEX_SECTION' => 'Y', // Индексировать разделы для модуля поиска
            'INDEX_ELEMENT' => 'Y', // Индексировать элементы для модуля поиска
            'VERSION' => 2,
        );
        $ib = new \CIBlock;
        $iblockId = $ib->Add($arFields);
        \Bitrix\Main\Config\Option::set($this->MODULE_ID, 'test_iblock_id', $iblockId);
    }

    public function UnInstallDB()
    {
        $ib = new \CIBlock;
        $iblockId = \Bitrix\Main\Config\Option::get($this->MODULE_ID, 'test_iblock_id');
        $ib->Delete($iblockId);
    }

    public function InstallEvents()
    {
        $eventManager = EventManager::getInstance();

        $eventManager->registerEventHandler(
            'crm',
            'onEntityDetailsTabsInitialized',
            $this->MODULE_ID,
            '\\Otus\\Homework\\Crm\\Handlers',
            'updateTabs'
        );

        return true;
    }

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

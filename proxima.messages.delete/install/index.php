<?php

use Bitrix\Main\Application;
use Bitrix\Main\ModuleManager;

class proxima_messages_delete extends CModule
{
    /**
     * delete_message constructor.
     */
    public function __construct()
    {
        $this->MODULE_ID = 'proxima.messages.delete';
        $this->MODULE_NAME = '[Proxima] Очистка чатов';
        $this->MODULE_DESCRIPTION = 'Модуль для очистки чатов';
        $arModuleVersion = [];
        include(__DIR__ . '/version.php');
        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }
        $this->PARTNER_NAME = 'Proxima';
        $this->PARTNER_URI = 'https://proxima.ooo';
    }


    /**
     *
     */
    public function DoInstall()
    {
        if (!ModuleManager::isModuleInstalled($this->MODULE_ID)) {
            $this->InstallDB();
            CAdminMessage::ShowNote('Модуль установлен');
        } else {
            CAdminMessage::ShowNote('Ошибка установки модуля');
        }
        ModuleManager::registerModule($this->MODULE_ID);
    }

    /**
     * Create DB tables
     * @return bool
     */
    public function InstallDB()
    {
        try {
            $db = Application::getConnection();

            $sqlFilePath = __DIR__.'/db/install.sql';
            if(!file_exists($sqlFilePath)) {
                $GLOBALS["APPLICATION"]->ThrowException('Не найден файл'.' '.$sqlFilePath);
                return false;
            }
            $errors = $db->executeSqlBatch(file_get_contents($sqlFilePath));
            if(!empty($errors)) {
                $GLOBALS["APPLICATION"]->ThrowException('Ошибка при выполнении запроса'.' '.var_export($errors, true));
                return false;
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     *
     */
    public function DoUninstall()
    {
        if (ModuleManager::isModuleInstalled($this->MODULE_ID)) {
            $this->UnInstallDB();
            CAdminMessage::ShowNote('Модуль удален');
        } else {
            CAdminMessage::ShowNote('Ошибка удаления модуля');
        }
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    /**
     * Delete DB tables
     * @return bool
     */
    public function UnInstallDB()
    {
        try {
            $db = Application::getConnection();

            $sqlFilePath = __DIR__.'/db/uninstall.sql';
            if(!file_exists($sqlFilePath)) {
                $GLOBALS["APPLICATION"]->ThrowException('Не найден файл'.' '.$sqlFilePath);
                return false;
            }
            $errors = $db->executeSqlBatch(file_get_contents($sqlFilePath));
            if(!empty($errors)) {
                $GLOBALS["APPLICATION"]->ThrowException('Ошибка при выполнении запроса'.' '.var_export($errors, true));
                return false;
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

}
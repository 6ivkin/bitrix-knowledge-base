<?php

use Bitrix\Main\Application;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\EventManager;

class proxima_task_module extends CModule
{
    /**
     * task_module constructor.
     */
    public function __construct()
    {
        $this->MODULE_ID = 'proxima.task.module';
        $this->MODULE_NAME = '[Proxima] Автоматическое напоминание для HR';
        $this->MODULE_DESCRIPTION = 'Автоматическая постановка задач';
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
            CAdminMessage::ShowNote('Модуль установлен');
        } else {
            CAdminMessage::ShowNote('Ошибка установки модуля');
        }
        ModuleManager::registerModule($this->MODULE_ID);
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler("main", "OnAfterUserRegister", "proxima.task.module", "AutoTask", "OnAfterUserRegisterHandler");
    }

    /**
     *
     */
    public function DoUninstall()
    {
        if (ModuleManager::isModuleInstalled($this->MODULE_ID)) {
            CAdminMessage::ShowNote('Модуль удален');
        } else {
            CAdminMessage::ShowNote('Ошибка удаления модуля');
        }
        ModuleManager::unRegisterModule($this->MODULE_ID);
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler("main", "OnAfterUserRegister", "proxima.task.module", "AutoTask", "OnAfterUserRegisterHandler");
    }


}
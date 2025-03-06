<?php
/**
 * Подключается при подключении модуля через \Bitrix\Main\Loader::includeModule('module.name');
 */

class AutoTask
{
    // создаем обработчик события "OnAfterUserRegister"
    public static function OnAfterUserRegisterHandler(&$arFields)
    {
        // если регистрация успешна то
        if ($arFields["USER_ID"] > 0) {
            if (CModule::IncludeModule("tasks")) {
                $arFields = array(
                    "TITLE" => "Интервью с новым сотрудником " . $arFields['LAST_NAME'] . $arFields['NAME'],
                    "DESCRIPTION" => "Через 2 недели нужно провести повторное интервью с" . $arFields['LAST_NAME'] . $arFields['NAME'] . "сотрудником и также через месяц",
                    "RESPONSIBLE_ID" => 926, // Мухамбетова
                    "ACCOMPLICES" => 472, // Осадчая
                );
                $obTask = new CTasks;
                $ID = $obTask->Add($arFields);
                $success = ($ID > 0);
                if ($success) {
                    echo "Ok!";
                } else {
                    echo "Error";
                }
            }
        }
        return $arFields;
    }
}

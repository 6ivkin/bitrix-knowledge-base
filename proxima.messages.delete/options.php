<?php

use Bitrix\Main\Loader;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\UserTable;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\Main\ORM\Fields\ExpressionField;

defined('B_PROLOG_INCLUDED') || die;

global $APPLICATION, $USER;

$moduleId = strval($_GET['mid']);
if (!$USER->IsAdmin())
    return;

$result = array();
$users = UserTable::query()
    ->addSelect('ID')
    ->addSelect('USER_FULL_NAME')
    ->whereNull('EXTERNAL_AUTH_ID') 
    ->whereNotLike('XML_ID', 'livechat%')
    ->registerRuntimeField('USER_FULL_NAME', new Fields\ExpressionField('USER_FULL_NAME', "CONCAT_WS(' ', %s, %s)", array('LAST_NAME', 'NAME')))
    ->exec();

while ($user = $users->fetch()) {
    $result[$user['ID']] = $user['USER_FULL_NAME'];
}

Loader::includeModule($moduleId);
$arTabs = array(
    array(
        'DIV' => 'general',
        'TAB' => 'Основные',
        'TITLE' => 'Основные настройки',
        'OPTIONS' => array(

            array(
                'note' => 'Настройка позволяет указать интервал (в месяцах), за который сохранятся сообщения
                            (по умолчанию сообщения сохраняются за последние 6 месяцев'
            ),
            array(
                'months',
                'Интервал:',
                6,
                array('text', 3),
            ),

            array(
                'note' => 'Настройка позволяет выбрать пользователей, у которых будут удалены/сохранены сообщения
                            (по умолчанию сообщения удаляются у всех пользователей'
            ),
            array(
                'users',
                'Пользователи:',
                '',
                array('multiselectbox', $result)
            ),

            array(
                'note' => 'Настройка позволяет изменять режим удаления/сохранения сообщений у выбранных пользователей
                             (по умолчанию включен режим удаления, снятие галочки позволит сохранить сообщения 
                             (сообщения остальных пользователей будут удалены!))'
            ),
            array(
                'switch_on',
                'Удалить сообщения:',
                'Y',
                array('checkbox')
            ),
            array(
                'note' => 'При нажатие на кнопку "Удалить" произойдет моментальное удаление сообщений
                            по заданным параметрам!'
            ),
        )
    )
);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && strlen($_REQUEST['save']) > 0 && check_bitrix_sessid()) {
    foreach ($arTabs as $arTab) {
        __AdmSettingsSaveOptions($moduleId, $arTab['OPTIONS']);
    }

    LocalRedirect($APPLICATION->GetCurPageParam());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && strlen($_REQUEST['delete']) > 0 && check_bitrix_sessid()) {
    
    foreach ($arTabs as $arTab) {
        __AdmSettingsSaveOptions($moduleId, $arTab['OPTIONS']);
    }
    
    Proxima\Messages\Delete\Main::delete();

    LocalRedirect($APPLICATION->GetCurPageParam());
}


$tabControl = new CAdminTabControl("tabControl", $arTabs);

$tabControl->Begin();
?>

<form action="<?= $APPLICATION->GetCurPage(); ?>?mid=<?= $moduleId; ?>&lang=ru"
      method="post">
    <?php
    foreach ($arTabs as $arTab) {
        if ($arTab['OPTIONS']) {
            $tabControl->BeginNextTab();
            __AdmSettingsDrawList($moduleId, $arTab['OPTIONS']);
        }
    }
    ?>
    <?
    $tabControl->Buttons(['btnApply' => false, 'btnCancel' => false, 'btnSaveAndAdd' => false]);?>
    <input type="submit" name="delete" value="Удалить" title="Удалить сообщения" class="adm-btn-apply">
    <?
    echo(bitrix_sessid_post());
    $tabControl->End()
    ?>
    
</form>

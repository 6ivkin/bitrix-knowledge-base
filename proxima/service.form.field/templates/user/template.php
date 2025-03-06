<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UI\Extension;
use Proxima\Service\Component\Form\UserField;

Extension::load(['ui.forms', 'proxima.bootstrap4']);

/**@var CAllMain $APPLICATION */
/**@var CBitrixComponentTemplate $this */
/**@var CITBServiceFormField $component */
/**@var UserField $field */
$component = $this->getComponent();
$field = $component->getField();
?>
<?php if ($field): ?>
    <label for="field-<?= $field->getCssName() ?>" class="text-muted m-0">
        <?= $field->getTitle() ?>
        <? if ($field->isRequired()): ?>
            <span class="text-danger">*</span>
        <? endif; ?>
    </label>
    <?php
    $values = [];
    if ($field->isUseSymbolic()) {
        $values = $field->getValue();
    } else {
        foreach ($field->getValue() as $id) {
            $values[] = $id;
        }
    }
    $APPLICATION->IncludeComponent(
        'bitrix:main.user.selector',
        ' ',
        [
            'ID' => 'field-' . $field->getCssName(),
            'API_VERSION' => 3,
            'LIST' => $values,
            'INPUT_NAME' => 'DATA[' . $field->getName() . ']' . (($field->isMultiple()) ? '[]' : ''),
            'USE_SYMBOLIC_ID' => $field->isUseSymbolic(),
            'OPEN_DIALOG_WHEN_INIT' => false,
            'LAZYLOAD' => 'Y',
            'LOCK' => $field->isDisabled(),
            'READONLY' => $field->isDisabled(),
            'SELECTOR_OPTIONS' => [
                //'contextCode' => 'U',
                'enableUsers' => $field->isDisableUsers() ? 'N' : 'Y',
                'enableDepartments' => $field->isEnableDepartments() ? 'Y' : 'N',
                'departmentSelectDisable' => $field->isEnableDepartments() ? 'N' : 'Y',
                'departmentFlatEnable' => 'Y',
                'enableAll' => 'N',
            ]
        ]
    );
    ?>
    <small id="field-<?= $field->getCssName() ?>-help" class="form-text text-muted"><?= $field->getDescription() ?></small>
<?php endif; ?>
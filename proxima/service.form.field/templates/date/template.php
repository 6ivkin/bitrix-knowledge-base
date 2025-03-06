<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UI\Extension;
use Proxima\Service\Component\Form\DateField;

Extension::load(['ui.forms', 'proxima.bootstrap4', 'date']);

/**@var CAllMain $APPLICATION */
/**@var CBitrixComponentTemplate $this */
/**@var CITBServiceFormField $component */
/**@var DateField $field */
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
    <?php ?>
    <div class="ui-ctl ui-ctl-datetime ui-ctl-w100 ui-ctl-after-icon">
        <div class="ui-ctl-after ui-ctl-icon-calendar"></div>
        <input id="field-<?= $field->getCssName() ?>"
               class="ui-ctl-element"
               type="text"
               name="DATA[<?= $field->getName() ?>]"
               value="<?= $field->getValue() ?>"
               onclick="BX.calendar({node: this, field: this, bTime: <?= $field->isUseTime() ? 'true' : 'false' ?>, bHideTime: <?= $field->isUseTime() ? 'false' : 'true' ?>});"
            <?= $field->isDisabled() ? 'disabled' : '' ?>
            <?= $field->isRequired() ? 'required' : '' ?>
        >
    </div>
    <small id="field-<?= $field->getCssName() ?>-help" class="form-text text-muted"><?= $field->getDescription() ?></small>
<?php endif; ?>
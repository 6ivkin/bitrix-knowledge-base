<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UI\Extension;
use Proxima\Service\Component\Form\BoolField;

Extension::load(['ui.forms', 'proxima.bootstrap4']);

/**@var CAllMain $APPLICATION */
/**@var CBitrixComponentTemplate $this */
/**@var CITBServiceFormField $component */
/**@var BoolField $field */
$component = $this->getComponent();
$field = $component->getField();
?>
<?php if ($field): ?>
    <label for="field-<?= $field->getCssName() ?>" class="ui-ctl ui-ctl-checkbox text-muted m-0">
        <input id="field-<?= $field->getCssName() ?>"
               type="checkbox"
               class="ui-ctl-element"
               name="DATA[<?= $field->getName() ?>]"
               value="1"
            <?= ($field->getValue()) ? 'checked' : '' ?>
            <?= ($field->isRequired()) ? 'required' : '' ?>
            <?= ($field->isDisabled()) ? 'disabled' : '' ?>
        >
        <div class="ui-ctl-label-text">
            <?= $field->getTitle() ?>
            <?php if ($field->isRequired()): ?>
                <span class="text-danger">*</span>
            <?php endif; ?>
        </div>
    </label>
    <small id="field-<?= $field->getCssName() ?>-help" class="form-text text-muted"><?= $field->getDescription() ?></small>
<?php endif; ?>
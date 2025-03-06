<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UI\Extension;
use Proxima\Service\Component\Form\StringField;

Extension::load(['ui.forms', 'proxima.bootstrap4']);

/**@var CAllMain $APPLICATION */
/**@var CBitrixComponentTemplate $this */
/**@var CITBServiceFormField $component */
/**@var StringField $field */
$component = $this->getComponent();
$field = $component->getField();
?>
<?php if ($field): ?>
    <label for="field-<?= $field->getCssName() ?>" class="text-muted m-0">
        <?= $field->getTitle() ?>
        <?php if ($field->isRequired()): ?>
            <span class="text-danger">*</span>
        <?php endif; ?>
    </label>
    <div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
        <input id="field-<?= $field->getCssName() ?>"
               class="ui-ctl-element"
               type="text"
               name="DATA[<?= $field->getName() ?>]"
               value="<?= htmlspecialchars($field->getValue()) ?>"
               placeholder="<?= $field->getPlaceholder() ?>"
            <?php if (!empty($field->getPattern())): ?>
                   pattern="<?= $field->getPattern() ?>"
            <?php endif; ?>
            <?php if (!empty($field->getMaxLength())): ?>
                    maxlength="<?= $field->getMaxLength() ?>"
            <?php endif; ?>
            <?= $field->isDisabled() ? 'disabled' : '' ?>
            <?= $field->isRequired() ? 'required' : '' ?>
        >
    </div>
    <small id="field-<?= $field->getCssName() ?>-help" class="form-text text-muted"><?= $field->getDescription() ?></small>
<?php endif; ?>
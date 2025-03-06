<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UI\Extension;
use Proxima\Service\Component\Form\StringField;
use Proxima\Service\Component\Form\TextField;

Extension::load(['ui.forms', 'proxima.bootstrap4']);

/**@var CAllMain $APPLICATION */
/**@var CBitrixComponentTemplate $this */
/**@var CITBServiceFormField $component */
/**@var TextField $field */
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
    <div class="ui-ctl ui-ctl-textarea ui-ctl-w100">
        <textarea id="field-<?= $field->getCssName() ?>"
                  class="ui-ctl-element"
                  name="DATA[<?= $field->getName() ?>]"
                  <?php if (!empty($field->getMaxLength())): ?>
                      maxlength="<?= $field->getMaxLength() ?>"
                  <?php endif; ?>
                  <?= $field->isDisabled() ? 'disabled' : '' ?>
            <?= $field->isRequired() ? 'required' : '' ?>
        ><?= htmlspecialchars($field->getValue()) ?></textarea>
    </div>
    <small id="field-<?= $field->getCssName() ?>-help" class="form-text text-muted"><?= $field->getDescription() ?></small>
<?php endif; ?>
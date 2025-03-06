<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UI\Extension;
use Proxima\Service\Component\Form\SelectField;

Extension::load(['ui.forms', 'proxima.bootstrap4']);

/**@var CAllMain $APPLICATION */
/**@var CBitrixComponentTemplate $this */
/**@var CITBServiceFormField $component */
/**@var SelectField $field */
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
    <?php if ($field->getTheme() === $field::CHECKLIST): ?>
        <?php if ($field->isMultiple()): ?>
            <?php foreach($field->getItems() as $id => $value): ?>
                <div>
                    <label class="ui-ctl ui-ctl-checkbox">
                        <input id="field-<?= $field->getCssName() ?>-item-<?= $id ?>" type="checkbox" class="ui-ctl-element" name="DATA[<?= $field->getName() ?>][]" value="<?= $id ?>" <?= in_array($id, $field->getValue()) ? 'checked' : '' ?>>
                        <div class="ui-ctl-label-text"><?= $value ?></div>
                    </label>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <?php foreach($field->getItems() as $id => $value): ?>
                <div>
                    <label class="ui-ctl ui-ctl-radio">
                        <input id="field-<?= $field->getCssName() ?>-item-<?= $id ?>" type="radio" class="ui-ctl-element" name="DATA[<?= $field->getName() ?>]" value="<?= $id ?>" <?= in_array($id, $field->getValue()) ? 'checked' : '' ?>>
                        <div class="ui-ctl-label-text"><?= $value ?></div>
                    </label>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php else: ?>
        <div class="ui-ctl ui-ctl-w100 ui-ctl-after-icon ui-ctl-dropdown">
            <div class="ui-ctl-after ui-ctl-icon-angle"></div>
            <select id="field-<?= $field->getCssName() ?>"
                    class="ui-ctl-element"
                    name="DATA[<?= $field->getName() ?>]<?= $field->isMultiple() ? '[]' : '' ?>"
                    size="<?= $field->getSize() ?>"
                <?= $field->isDisabled() ? 'disabled' : '' ?>
                <?= $field->isRequired() ? 'required' : '' ?>
                <?= $field->isMultiple() ? 'multiple' : '' ?>
            >
                <? if ($field->isUseEmpty()): ?>
                    <option value=""></option>
                <? endif; ?>
                <? foreach ($field->getItems() as $id => $name): ?>
                    <option value="<?= $id ?>" <?= in_array($id, $field->getValue()) ? 'selected' : '' ?> ><?= $name ?></option>
                <? endforeach; ?>
            </select>
        </div>
    <?php endif; ?>
    <small id="field-<?= $field->getCssName() ?>-help" class="form-text text-muted"><?= $field->getDescription() ?></small>
<?php endif; ?>
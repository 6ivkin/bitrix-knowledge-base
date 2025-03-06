<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UI\Extension;
use Proxima\Service\Component\Form\EntityField;

Extension::load(['ui.forms', 'proxima.bootstrap4', 'ui.entity-selector']);

/**@var CAllMain $APPLICATION */
/**@var CBitrixComponentTemplate $this */
/**@var CITBServiceFormField $component */
/**@var EntityField $field */
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
    <div id="field-<?= $field->getCssName() ?>-selector"></div>
    <input id="field-<?= $field->getName() ?>-value" type="hidden" name="DATA[<?= $field->getName() ?>]" value="<?= htmlspecialchars(json_encode($field->getValue())) ?>">
    <small id="field-<?= $field->getCssName() ?>-help" class="form-text text-muted"><?= $field->getDescription() ?></small>
    <script>
        let tagSelector_<?= $field->getName() ?> = new BX.UI.EntitySelector.TagSelector({
            textBoxAutoHide: false,
            textBoxWidth: 350,
            maxHeight: 99,
            placeholder: 'введите название',
            addButtonCaption: 'Выбрать',
            addButtonCaptionMore: 'Добавить еще',
            showCreateButton: false,
            multiple: <?= $field->isMultiple() ? 'true' : 'false'; ?>,
            readonly: <?= $field->isDisabled() ? 'true' : 'false'; ?>,
            locked: false,
            deselectable: <?= $field->isDeselectable() ? 'true' : 'false'; ?>,
            items: [

            ],
            events: {
                onTagAdd: function(event) {
                    const selector = event.getTarget();
                    let input = document.getElementById('field-<?= $field->getName() ?>-value');
                    let values = [];
                    let items = selector.getTags();
                    items.forEach(function (item) {
                        values.push({
                            id: item.id,
                            entityId: item.entityId,
                            title: item.title,
                        });
                    });
                    console.log(values);
                    input.value = JSON.stringify(values);
                },
                onTagRemove: function(event) {
                    const selector = event.getTarget();
                    let input = document.getElementById('field-<?= $field->getName() ?>-value');
                    let values = [];
                    let items = selector.getTags();
                    items.forEach(function (item) {
                        values.push({
                            id: item.id,
                            entityId: item.entityId,
                            title: item.title,
                        });
                    });
                    console.log(values);
                    input.value = JSON.stringify(values);
                }
            },
            dialogOptions: {
                context: '<?= $field->getContext() ?>',
                preselectedItems: [
                    <?php foreach ($field->getValue() as $item): ?>
                    ['<?= $item['entityId'] ?>', <?= $item['id'] ?>],
                    <?php endforeach; ?>
                ],
                <?php if (!$field->isDeselectable()): ?>
                undeselectedItems: [
                    <?php foreach ($field->getValue() as $item): ?>
                    ['<?= $item['entityId'] ?>', <?= $item['id'] ?>],
                    <?php endforeach; ?>
                ],
                <?php endif; ?>
                entities: [
                    <?php foreach ($field->getEntities() as $entity): ?>
                    {
                        id: '<?= $entity['id'] ?>',
                        options: {
                        <?php if (is_array($entity['options'])): ?>
                            <?php foreach ($entity['options'] as $option => $value): ?>
                            '<?= $option ?>': '<?= $value ?>',
                            <?php endforeach; ?>
                        <?php endif; ?>
                        }
                    },
                    <?php endforeach; ?>
                ],
            }
        });
        tagSelector_<?= $field->getName() ?>.renderTo(document.getElementById('field-<?= $field->getCssName() ?>-selector'));
    </script>
<?php endif; ?>
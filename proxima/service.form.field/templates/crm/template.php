<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UI\Extension;
use Proxima\Service\Component\Form\CrmField;

Extension::load(['ui.forms', 'proxima.bootstrap4']);

/**@var CAllMain $APPLICATION */
/**@var CBitrixComponentTemplate $this */
/**@var CITBServiceFormField $component */
/**@var CrmField $field */
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

    $options = [];
    if ($field->isUseLeads()) {
        $options['enableCrmLeads'] = 'Y';
        $options['addTabCrmLeads'] = 'Y';
    }
    if ($field->isUseDeals()) {
        $options['enableCrmDeals'] = 'Y';
        $options['addTabCrmDeals'] = 'Y';
    }
    if ($field->isUseContacts()) {
        $options['enableCrmContacts'] = 'Y';
        $options['addTabCrmContacts'] = 'Y';
    }
    if ($field->isUseCompanies()) {
        $options['enableCrmCompanies'] = 'Y';
        $options['addTabCrmCompanies'] = 'Y';
    }
    if ($field->isUseProducts()) {
        $options['enableCrmProducts'] = 'Y';
        $options['addTabCrmProducts'] = 'Y';
    }
    if ($field->isUseQuotes()) {
        $options['enableCrmQuotes'] = 'Y';
        $options['addTabCrmQuotes'] = 'Y';
    }
    if ($field->isUseOrders()) {
        $options['enableCrmOrders'] = 'Y';
        $options['addTabCrmOrders'] = 'Y';
    }
    if ($field->isUseOnlyMyCompanies()) {
        $options['onlyMyCompanies'] = 'Y';
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
            'SELECTOR_OPTIONS' => array_merge(
                [
                    'contextCode' => 'CRM',
                    'enableUsers' => 'N',
                    'enableAll' => 'N',
                    'enableDepartments' => 'N',
                    'enableCrm' => 'Y',
                    'crmPrefixType' => 'SHORT',
                ],
                $options
            )
        ]
    );
    ?>
    <small id="field-<?= $field->getCssName() ?>-help" class="form-text text-muted"><?= $field->getDescription() ?></small>
<?php endif; ?>
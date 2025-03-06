<?php

use Bitrix\Main\Loader;
use Proxima\Service\Component\Form\EntityField;
use Proxima\Service\Component\Form\Field;
use Proxima\Service\Component\Simple;

if(!Loader::includeModule('proxima.service')) {
    throw new Exception('Ошибка подключения модуля proxima.service');
}

/**
 * Class CITBServiceFormField
 */
class CITBServiceFormField extends Simple
{
    protected ?Field $field;

    /**
     * @param $component
     */
    public function __construct($component = null)
    {
        parent::__construct($component);
        $this->field = null;
    }

    /**
     * @return mixed|void|null
     */
    public function executeComponent()
    {
        $template = '.default';
        try {
            if (is_array($this->arParams['FIELD'])) {
                $this->field = Field::create($this->arParams['FIELD']);
            } else {
                if (is_object($this->arParams['FIELD']) && is_subclass_of($this->arParams['FIELD'], Field::class)) {
                    $this->field = $this->arParams['FIELD'];
                }
            }
            if (is_a($this->field, EntityField::class)) {
                $this->field->includeModules();
            }
            if (!$this->getField()) {
                throw new Exception('Ошибка инициализации поля из параметров ' . print_r($this->arParams['FIELD'], true));
            }
            $template = $this->getField()->getType();
        } catch (Exception $e) {
            $this->addErrorCompatible($e->getMessage());
        }
        $this->setTemplateName($template);
        $this->IncludeComponentTemplate();
    }

    /**
     * @return Field|null
     */
    public function getField(): ?Field
    {
        return $this->field;
    }
}

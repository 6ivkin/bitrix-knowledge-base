<?php

use Bitrix\Main\Loader;
use Bitrix\Main\UserFieldTable;
use Bitrix\Crm\Service\Container;
use Bitrix\Crm\Attribute\FieldAttributeManager;
use Bitrix\Crm\Attribute\FieldOrigin;
use Bitrix\Crm\Attribute\FieldAttributeType;
use Proxima\Service\Component\Simple;
use Proxima\Service\Component\GridHelper;

if(!Loader::includeModule('proxima.service')) {
    throw new Exception('Ошибка подключения модуля proxima.service');
}

/**
 * Class CITBServiceCrmRequireFieldList
 */
class CITBServiceCrmRequireFieldList extends Simple
{
    protected array $requiredFields;
    protected array $ufData;
    protected array $entityTypesData;
    protected string $sortDirection;

    /**
     * @return mixed|void|null
     */
    public function executeComponent()
    {
        try {
            if(!Loader::includeModule('crm')) {
                throw new Exception('Ошибка подключения модуля crm');
            }

            $grid = new GridHelper("prox_service_crm_require_field_list123");
            $this->setGrid($grid);
            $this->requiredFields = [];
            $this->ufData = [];
            $this->entityTypesData = [];
            $container = Container::getInstance();
            $typesMap = $container->getTypesMap();
            $entityList = [];
            foreach ($typesMap->getFactories() as $factory) {
                if(!$factory->isStagesSupported())
                    continue;

                //категории
                $categories = [];
                if($factory->isCategoriesSupported()){
                    foreach ($factory->getCategories() as $category)
                    {
                        $categories[$category->getId()] = $category->getName();
                    }
                }
                if(count($categories)==0){
                    $categories = [0 => ''];
                }

                //стадии
                $stages = [];
                foreach($categories as $categoryId=>$categoryName)
                    foreach ($factory->getStages($categoryId) as $stage)
                    {
                        $stages[$stage->getId()] = [
                            'NAME' => $stage->getName(),
                            'STATUS_ID' => $stage->getStatusId(),
                            'SEMANTICS' => $stage->getSemantics(),
                            'CATEGORY_ID' => $stage->getCategoryId(),
                        ];
                    }

                //Для смарт-процессов
                $type = $container->getTypeByEntityTypeId($factory->getEntityTypeId());

                //параметры
                $entityList[$factory->getEntityTypeId()] = $factory->getEntityDescription();
                $this->entityTypesData[$factory->getEntityTypeId()] = [
                    'TYPE_ID' => isset($type)? $type->getId() : 0,
                    'ENTITY_TYPE_ID' => $factory->getEntityTypeId(),
                    'NAME' => $factory->getEntityName(),
                    'TITLE' => $factory->getEntityDescription(),
                    'CATEGORY_SUPPORTED' => $factory->isCategoriesSupported(),
                    'CATEGORIES' => $categories,
                    'STAGES' => $stages
                ];

                //echo "<pre>";print_r($this->entityTypesData[$factory->getEntityTypeId()]);echo "</pre>";
            }
            $grid->setFilter([
                [
                    'id'      => 'ENTITY_TYPE_ID',
                    'name'    => 'Сущность',
                    'type'    => 'list',
                    'items'   => $entityList,
                    'default' => true,
                    'params' => [
                        'multiple' => 'Y'
                    ],
                ],
            ])->setColumns([
                [
                    'id'              => 'ENTITY_TYPE_ID',
                    'name'            => 'ID сущности',
                    'sort'            => false,
                    'default'         => false,
                ],
                [
                    'id'              => 'ENTITY_TYPE_TITLE',
                    'name'            => 'Сущность',
                    'sort'            => false,
                    'default'         => true,
                ],
                [
                    'id'              => 'CATEGORY_ID',
                    'name'            => 'ID направления',
                    'sort'            => false,
                    'default'         => false,
                ],
                [
                    'id'              => 'CATEGORY_TITLE',
                    'name'            => 'Направление',
                    'sort'            => false,
                    'default'         => true,
                ],
                [
                    'id'              => 'FIELD_TITLE',
                    'name'            => 'Название поля',
                    'sort'            => 'FIELD_TITLE',
                    'default'         => true,
                ],
                [
                    'id'              => 'FIELD_NAME',
                    'name'            => 'Код поля',
                    'sort'            => false,
                    'default'         => true,
                ],
                [
                    'id'              => 'STAGES_REQUIRED',
                    'name'            => 'Обязательно для стадий ',
                    'sort'            => false,
                    'default'         => true,
                ],
            ]);

            $filterData = $grid->getFilterData();
            // по умолчанию показываем все
            if(!isset($filterData['ENTITY_TYPE_ID']) || count($filterData['ENTITY_TYPE_ID'])==0){
                $filterData['ENTITY_TYPE_ID'] = array_keys($entityList);
            }
            //echo "<pre>";print_r($filterData);echo "</pre>";

            foreach($this->entityTypesData as $entityTypeData){
                if(in_array($entityTypeData['ENTITY_TYPE_ID'],$filterData['ENTITY_TYPE_ID'])) {
                    $fieldsNames = $this->getRequiredFieldsForEntityType($entityTypeData);
                    $this->getUserFieldsNames($fieldsNames);
                }
            }

            foreach ($this->requiredFields as $key => $requiredField) {
                $ufData = $this->ufData[$requiredField['FIELD_NAME']];
                $this->requiredFields[$key]['FIELD_TITLE'] = $ufData['EDIT_FORM_LABEL']['ru'];
                $this->requiredFields[$key]['FIELD_ID'] = $ufData['ID'];
            }

            //сортировка
            $sort = $grid->getSort();
            $this->sortDirection = isset($sort['FIELD_TITLE']) ? $sort['FIELD_TITLE'] : 'asc';
            usort($this->requiredFields, [$this, "orderFieldsByFieldTitle"]);

            //echo "<pre>";print_r($this->requiredFields);echo "</pre>";

            foreach ($this->requiredFields as $requiredField) {
                $actions = [];
                if($requiredField['TYPE_ID']==0) {//штатные crm сущности
                    $url = '/crm/configs/fields/CRM_'.$requiredField['ENTITY_TYPE_NAME'].'/edit/'.$requiredField['FIELD_NAME'].'/';
                } else {//смарт процессы
                    if(stripos($requiredField['ENTITY_TYPE_NAME'],'DYNAMIC_')===false){
                        $url = '/configs/userfield.php?moduleId=crm&entityId=CRM_'.$requiredField['ENTITY_TYPE_NAME'].'&fieldId='.$requiredField['FIELD_ID'];
                    }else
                        $url = '/configs/userfield.php?moduleId=crm&entityId=CRM_'.$requiredField['TYPE_ID'].'&fieldId='.$requiredField['FIELD_ID'];
                }
                $data = [
                    'ENTITY_TYPE_ID' => $requiredField['ENTITY_TYPE_ID'],
                    'ENTITY_TYPE_TITLE'   => $requiredField['ENTITY_TYPE_TITLE'],
                    'CATEGORY_ID' => $requiredField['CATEGORY_ID'],
                    'CATEGORY_TITLE' => $requiredField['CATEGORY_TITLE'],
                    'FIELD_TITLE' => $requiredField['FIELD_TITLE'],
                    'FIELD_NAME' => '<a href="'.$url.'" target="_blank">'.$requiredField['FIELD_NAME'].'</a>',
                    'STAGES_REQUIRED' => implode('</br>',$requiredField['STAGES_REQUIRED'])
                ];

                $grid->addRow([
                    'data' => $data,
                    'actions' => $actions,
                ]);
            }

            $grid->getNavigation()->setRecordCount(count($this->requiredFields));

        } catch (Exception $e) {
            $this->addErrorCompatible($e->getMessage());
        }
        $this->IncludeComponentTemplate();
    }

    private function orderFieldsByFieldTitle(array $a, array $b):int
    {
        if($this->sortDirection=='asc')
            return strcasecmp($a["FIELD_TITLE"], $b["FIELD_TITLE"]);
        else
            return strcasecmp($b["FIELD_TITLE"], $a["FIELD_TITLE"]);
    }

    private function getRequiredFieldsForEntityType(array $entityTypeData):array
    {
        $fieldsNames = [];
        $categories = $entityTypeData['CATEGORIES'];
        $stages = $entityTypeData['STAGES'];
        $typeId = $entityTypeData['TYPE_ID'];
        foreach($categories as $categoryId => $categoryTitle) {

            $allRequitedProps = FieldAttributeManager::getList(
                $entityTypeData['ENTITY_TYPE_ID'],
                $entityTypeData['CATEGORY_SUPPORTED'] ? 'category_'.$categoryId : '',
                FieldOrigin::CUSTOM,
                FieldAttributeType::REQUIRED
            );


            $arRequired = [];
            foreach ($allRequitedProps as $RequitedProps) {
                //echo '<pre>'; print_r($RequitedProps); echo "</pre>";
                if(!isset($fieldsNames[$RequitedProps['FIELD_NAME']][$categoryId])){
                    if(!is_array($fieldsNames[$RequitedProps['FIELD_NAME']]))
                        $fieldsNames[$RequitedProps['FIELD_NAME']] = [];
                    $fieldsNames[$RequitedProps['FIELD_NAME']][$categoryId] = count($this->requiredFields);
                    $this->requiredFields[] = [
                        'TYPE_ID' => $typeId,
                        'ENTITY_TYPE_ID' => $entityTypeData['ENTITY_TYPE_ID'],
                        'ENTITY_TYPE_TITLE' => $entityTypeData['TITLE'],
                        'ENTITY_TYPE_NAME' => $entityTypeData['NAME'],
                        'CATEGORY_ID' => $categoryId,
                        'CATEGORY_TITLE' => $categoryTitle,
                        'FIELD_NAME' => $RequitedProps['FIELD_NAME'],
                        'STAGES_REQUIRED' => [],
                    ];
                }

                $arStages = $this->requiredFields[$fieldsNames[$RequitedProps['FIELD_NAME']][$categoryId]]['STAGES_REQUIRED'];
                $add = false;
                foreach($stages as $stage){
                    if($stage['STATUS_ID']==$RequitedProps['START_PHASE' ])
                        $add=true;
                    if($add)
                        $arStages[] = $stage['NAME'];
                    if($stage['STATUS_ID']==$RequitedProps['FINISH_PHASE' ])
                        break;
                }
                $this->requiredFields[$fieldsNames[$RequitedProps['FIELD_NAME']][$categoryId]]['STAGES_REQUIRED'] = $arStages;
            }
        }

        return array_keys($fieldsNames);
    }

    private function getUserFieldsNames(array $fieldsNames):void
    {
        //Получаем название обязательных полей
        $dbUserFields = UserFieldTable::getList(array(
            //'filter' => array('ENTITY_ID' => 'CRM_3'/*'CRM_DEAL'*/),
            'filter' => array('FIELD_NAME'=> $fieldsNames),
            'select' => array('ID','ENTITY_ID','FIELD_NAME')
        ));
        while ($arUserField = $dbUserFields->fetch()) {

            $arUserField = \CUserTypeEntity::GetByID($arUserField['ID']);
            $this->ufData[$arUserField["FIELD_NAME"]] = $arUserField;
            //echo  $arUserField['ID']." ".$arUserField['ENTITY_ID']." ". $arUserField["EDIT_FORM_LABEL"]["ru"]." ".$arUserField["FIELD_NAME"]."</br>";
        }
    }
}

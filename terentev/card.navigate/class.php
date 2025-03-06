<?php

/** Класс компонента: Карточка навигации с правой стороны страницы */

namespace Terentev;
use Bitrix\Main\Application;
use CIBlockElement;

class TerentevCardNavigate extends \CBitrixComponent {

    public function executeComponent()  
    {
        $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
        $sectionId = $request->get('id');
        $iblockId = 173; // ID инфоблока
        $filter = [
            'IBLOCK_ID' => $iblockId,
            'ACTIVE' => 'Y',
            'SECTION_ID' => $sectionId,
        ];

        $sort = [
            'SORT' => 'ASC',
        ];

        $newsElements = $this->getElements($filter, false, true, $sort);
        // Получаем элементы
        
        $newsData = [];
        foreach ($newsElements as $element) {
            $newsData[$element['ID']] = [
                'ID' => $element['ID'],
                'NAME' => $element['PROPERTY_TITLE_VALUE'] ?? $element['NAME'],
                'PROPERTY' => $element['PROPERTY_BLOCK_TYPE_ENUM_ID'],
            ];
        }

        $this->arResult['ELEMENTS'] = $newsData;
        $this->IncludeComponentTemplate();
    }

    /**
     * Функция для получения ID элемента по имени
     * @param string $name Название элемента
     * @param array $elements Массив элементов
     * @return int ID элемента
     */
    public function getElementIdByName($name, $elements)
    {
        // Перебираем элементы, чтобы найти ID по имени
        foreach ($elements as $element) {
            if ($element['NAME'] === $name) {
                return $element['ID'];  // Возвращаем ID для найденного имени
            }
        }
        return null;  // Если ID не найден
    }

    function getElements($arFilter, $indexField = false, $collectMultiple = true, $sort = [], $navParams = [])
    {
        $indexField = $collectMultiple && $indexField ? $indexField : ($indexField ?: 'ID');
        $indexFieldSelect = (strpos($indexField, '_VALUE') !== false ? str_replace('_VALUE', '', $indexField) : '');

        $result = [];
        $arFilter['ACTIVE'] = $arFilter['ACTIVE'] == 'ALL' ? '' : ($arFilter['ACTIVE'] ?: 'Y');
        $arSelect = ['*', ($indexFieldSelect ? $indexFieldSelect : '')];

        if ($navParams) {
            $resNav = \CIBlockElement::GetList($sort, $arFilter, ['ID'], $navParams);
            while ($item = $resNav->Fetch()) {
                $navIds[] = $item['ID'];
            }
            $arFilter['ID'] = $navIds;
        }
        $elements = [];

        $res = \CIBlockElement::GetList($sort, $arFilter, false, false, $arSelect);
        while ($item = $res->Fetch()) {
            if ($collectMultiple) {
                $item['PROPERTIES'] = [];

                if (!$elements[$item[$indexField]])
                    $elements[$item[$indexField]] =& $item;

                unset($item);
            } elseif ($indexField)
                $elements[$item[$indexField]] = $item;
            else
                $elements[] = $item;
        }

        if ($collectMultiple) {
            \CIBlockElement::GetPropertyValuesArray($elements, $arFilter['IBLOCK_ID'], $arFilter);
            unset($res, $arFilter, $sort, $arSelect);

            foreach ($elements as &$element) {
                foreach ($element['PROPERTIES'] as $code => $property) {
                    if ($property['MULTIPLE'] == 'Y' && $property['VALUE']) {
                        foreach ($property['VALUE'] as $key => $value) {
                            if ($property['WITH_DESCRIPTION'] == 'Y') {
                                $element['PROPERTY_' . $code][$key] = [
                                    'VALUE' => $value,
                                    'DESCRIPTION' => $property['DESCRIPTION'][$key]
                                ];
                            } else {
                                $element['PROPERTY_' . $code][$property['PROPERTY_VALUE_ID'][$key]] = $value;
                            }
                        }
                    } else {
                        if ($property['VALUE']) {
                            $element['PROPERTY_' . $code . '_VALUE_ID'] = $property['PROPERTY_VALUE_ID'];

                            if ($property['USER_TYPE'] == 'HTML') {
                                $element['PROPERTY_' . $code . '_VALUE']['TEXT'] = $property['~VALUE']['TEXT'];
                            } else {
                                if ($property['WITH_DESCRIPTION'] == 'Y' && $property['DESCRIPTION']) {
                                    $element['PROPERTY_' . $code] = [
                                        'VALUE' => $property['VALUE'],
                                        'DESCRIPTION' => $property['DESCRIPTION']
                                    ];
                                } else {
                                    $element['PROPERTY_' . $code . '_VALUE'] = $property['VALUE'];
                                }
                            }

                            if ($property['PROPERTY_TYPE'] == 'L')
                                $element['PROPERTY_' . $code . '_ENUM_ID'] = $property['VALUE_ENUM_ID'];
                        }
                    }
                }

                unset($element['PROPERTIES']);
            }
        }

        $result = $elements;
        unset($elements);

        return $result;

    }

    public function configureActions()
    {
        return [];
    }
}
?>

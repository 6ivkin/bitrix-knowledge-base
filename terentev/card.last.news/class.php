<?

/** Класс компонента: Карточки в виде списка */

namespace Terentev;
use Bitrix\Main\Application;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\Response\AjaxJson;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

class TerentevCardMessagesComponent extends \CBitrixComponent implements Controllerable
{
    public function executeComponent()
    {
        $this->arResult['IMG_PATH_EYES'] = '/local/templates/new_wiki/assets/img';

        $iblockId = 173; // ID инфоблока
        $filter = [
            'IBLOCK_ID' => $iblockId,
            'ACTIVE' => 'Y',
            'PROPERTY_BLOCK_TYPE_VALUE' => 'Заголовок'
        ];
        $sort = [
            'TIMESTAMP_X' => 'DESC',
        ];
        $navParams = ['nTopCount' => 4]; // Ограничиваем выборку до 4 элементов

        // Получаем элементы
        $elements = $this->getElements($filter, false, true, $sort, $navParams);

        $result = [];
        foreach ($elements['ITEMS'] as $key => $element) {
            $result[$key] = [
                'ID' => $element['ID'],
                'NAME' => $element['PROPERTY_TITLE_VALUE'],
                'LAST_MODIFIED' => date('d.m.Y', strtotime($element['TIMESTAMP_X'])),
                'IS_MODIFIED' => $element['TIMESTAMP_X_UNIX'] > $element['DATE_CREATE_UNIX'] ? 'измененная' : 'новая',
                'PARENTS' => $this->getAllParents($element['IBLOCK_SECTION_ID'])
            ];
        }

        if ($this->StartResultCache(3600, Users::getId())) {
            $this->arResult['LAST_NEWS'] = $result;

            $this->arResult['IMPORTANT_MESSAGES'] = $this->getImportantMessages();
            
            $this->IncludeComponentTemplate();
        }

    }

    /**
     * Получение имени самого верхнего родителя секции.
     */
    public function getAllParents($sectionId)
    {
        $parents = [];

        while ($sectionId) {
            $section = \CIBlockSection::GetByID($sectionId)->Fetch();
            if ($section) {
                $parents[] = [
                    'ID' => $section['ID'],
                    'NAME' => $section['NAME'],
                    'IBLOCK_SECTION_ID' => $section['IBLOCK_SECTION_ID']
                ]; // Добавляем имя текущего родителя в массив
                $sectionId = $section['IBLOCK_SECTION_ID']; // Переходим к следующему родителю
            } else {
                break; // Если родитель не найден, выходим
            }
        }

        // Разворачиваем массив, чтобы порядок был от корневого к текущему
        return array_reverse($parents);
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

        //
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

        if ($navParams) {
            $result = [
                'ITEMS' => $result,
                'NAV' => $resNav->GetNavPrint(''),
            ];
        }

        return $result;

    }

    /**
     * Получение текста сообщений из инфоблока Bitrix -> Важные сообщения
     */
    public function getImportantMessages()
    {
        $iblockId = 173; // ID инфоблока

        // Шаг 1: Получаем раздел "Bitrix24" на верхнем уровне
        $bitrix24Section = \CIBlockSection::GetList(
            [],
            [
                'IBLOCK_ID' => $iblockId,
                'NAME' => 'Bitrix24',
                'ACTIVE' => 'Y',
                'SECTION_ID' => 0 // Верхний уровень
            ],
            false,
            ['ID', 'NAME']
        )->Fetch();

        // Шаг 2: Получаем раздел "Важные сообщения" внутри "Bitrix24"
        $importantMessagesSection = \CIBlockSection::GetList(
            [],
            [
                'IBLOCK_ID' => $iblockId,
                'NAME' => 'Важные сообщения',
                'ACTIVE' => 'Y',
                'SECTION_ID' => $bitrix24Section['ID'] // ID родительского раздела
            ],
            false,
            ['ID', 'NAME']
        )->Fetch();

        // Шаг 3: Получаем элементы внутри "Важные сообщения" с помощью getElements
        $filter = [
            'IBLOCK_ID' => $iblockId,
            'SECTION_ID' => $importantMessagesSection['ID'],
            'ACTIVE' => 'Y'
        ];
        $sort = [
            'LAST_MODIFIED' => 'DESC',
        ];
        $elements = $this->getElements($filter, false, true, $sort);

        // Извлекаем массив PROPERTY_TEXT из каждого элемента, если он существует
        $result = [];

        if (!empty($elements) && is_array($elements)) {
            foreach ($elements as $element) {
                $text = '';
                if (!empty($element['PROPERTY_TEXT'])) {
                    foreach ($element['PROPERTY_TEXT'] as $elText) {
                        $text .= $elText['TEXT'] . ' ';
                    }
                }
                $result[] = [
                    'NAME' => $element['NAME'],
                    'LAST_MODIFIED' => $element['DATE_ACTIVE_FROM'],
                    'IS_MODIFIED' => $element['TIMESTAMP_X_UNIX'] > $element['DATE_CREATE_UNIX'] ? 1 : 0,
                    'TEXT' => trim($text)
                ];
            }
        }
        return $result;
    }

    public function configureActions()
    {
        return [];
    }
}

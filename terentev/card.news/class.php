<?

/** Класс компонента: Карточка новости */

namespace Terentev;
use Bitrix\Main\Application;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\Response\AjaxJson;
use CIBlockElement;

class TerentevCardNewsComponent extends \CBitrixComponent implements Controllerable
{
    public function executeComponent()
    {
        $request = Application::getInstance()->getContext()->getRequest();
        $sectionId = $request->get('id');
        $iblockId = 173; // ID инфоблока

        $sectionDescription = $this->getSectionDescription($iblockId, $sectionId);

        $newsFilter = [
            'IBLOCK_ID' => $iblockId,
            'ACTIVE' => 'Y',
            'IBLOCK_SECTION_ID' => $sectionId,
        ];
        $sort = [
            'SORT' => 'ASC',
        ];

        $navParams = ['nTopCount' => 4]; // Ограничиваем выборку до 4 элементов

        // Получаем элементы
        $newsElements = $this->getElements($newsFilter, false, true, $sort);

        $types = [
            8327 => 'news__title',
            8328 => 'news__text',
            8334 => 'news__info',
            8335 => 'news__danger',
            8331 => 'news__photo'
        ];

        $newsData = [];
        foreach ($newsElements as $element) {
            $newsData[$element['ID']] = [
                'ID' => $element['ID'],
                'NAME' => $element['PROPERTY_TITLE_VALUE'] ?? $element['NAME'],
                'TEXT' => $element['PROPERTY_TEXT'] ?? [], // Title Надо добавить, иначе нет  заголовка!!!!!
                'DESCRIPTION' => $element['DESCRIPTION'],
                'PROPERTY' => $types[$element['PROPERTY_BLOCK_TYPE_ENUM_ID']],
                'PARENTS' => $this->getAllParents($element['IBLOCK_SECTION_ID']),
                'IMG' => $element['PROPERTY_FILES'] ?? '',
            ];
        }

        $breadcrumbsFilter = [
            'IBLOCK_ID' => $iblockId,
            'IBLOCK_SECTION_ID' => $sectionId,
            'ACTIVE' => 'Y',
        ];
        $breadcrumbsElements = $this->getElements($breadcrumbsFilter, false, true, [], $navParams);
        $breadcrumbs = [];
        foreach ($breadcrumbsElements as $key => $elem) {
            $breadcrumbs[$key] = [
                'NAME' => $elem['NAME'],
                'LAST_MODIFIED' => date('d.m.Y', strtotime($elem['TIMESTAMP_X'])),
                'IS_MODIFIED' => $elem['TIMESTAMP_X_UNIX'] > $elem['DATE_CREATE_UNIX'] ? 'измененная' : 'новая',
                'PARENTS' => $this->getAllParents($elem['IBLOCK_SECTION_ID']),
            ];
        }

        $this->arResult['LAST_NEWS'] = $breadcrumbs;
        // text
        $this->arResult['SEARCH_QUERY'] = $_REQUEST['query'] ?? '';
        $this->arResult['SECTION_DESCRIPTION'] = $sectionDescription;
        $this->arResult['ELEMENTS'] = $newsData;
        $this->IncludeComponentTemplate();
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
                $parents[] = $section['NAME']; // Добавляем имя текущего родителя в массив
                $sectionId = $section['IBLOCK_SECTION_ID']; // Переходим к следующему родителю
            } else {
                break; // Если родитель не найден, выходим
            }
        }
        // Разворачиваем массив, чтобы порядок был от корневого к текущему
        return array_reverse($parents);
    }

    public function searchAction($query)
    {
        $res = $this->getSearchResult(173, $query);
        if(!empty($res)) {
            return AjaxJson::createSuccess(array_slice($res, 0, 10));
        } else {
            return AjaxJson::createError(null, 'error');
        }   
    }

    function getSearchResult(int $iblockId, string $query): array
    {
        $filter = ['IBLOCK_ID' => $iblockId];
        $section = \CIBlockSection::GetList([], $filter);

        $result = [];
        // Регулярное выражение ищет любые слова, содержащие подстроку $query
        $pattern = '/\b\w*' . preg_quote($query, '/') . '\w*\b/iu';

        while ($el = $section->Fetch()) {
            // Проверяем и поле NAME, и, если оно установлено, PROPERTY_TITLE_VALUE
            if (
                (isset($el['NAME']) && preg_match($pattern, $el['NAME'])) ||
                (isset($el['PROPERTY_TITLE_VALUE']) && preg_match($pattern, $el['PROPERTY_TITLE_VALUE']))
            ) {
                $result[] = $el;
            }
        }
        return $result;
    }

    private function getSectionDescription($iblockId, $sectionId)
    {
        if (!$sectionId) {
            return '';
        }

        $section = \CIBlockSection::GetList(
            [],
            ['IBLOCK_ID' => $iblockId, 'ID' => $sectionId],
            false,
            ['ID', 'NAME', 'DESCRIPTION']
        )->Fetch();

        return $section['DESCRIPTION'] ?? '';
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

        return $result;

    }

    public function configureActions()
    {
        return [
            'search' => [
                'prefilters' => [
                    new HttpMethod([HttpMethod::METHOD_POST]),
                    new Csrf()
                ],
                'postfilters' => []
            ]
        ];
    }
}
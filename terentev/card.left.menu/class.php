<?

/** Класс компонента: Карточка левое меню */

namespace Terentev;
use Bitrix\Main\Application;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\Response\AjaxJson;

class TerentevCardLeftMenuComponent extends \CBitrixComponent implements Controllerable
{
    public function executeComponent()
    {
        $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
        $sectionId = $request->get('id');
        $result = self::getIblockSections(173);

        $this->arResult['ARR'] = $result;
        $text = '';

        foreach ($result as $elem) {
            $count = 0;
            self::getStructure($elem, $text, $count);
            $text .= '</div>';
        }

        $this->arResult['TEXT'] = $text;
        $this->IncludeComponentTemplate();


    }

    // не использую данное построение левого меню
    public function getStructure($arr, &$text, &$count)
    {
        $classes = [
            0 => '',
            1 => 'Two',
            2 => 'Three',
            3 => 'Four',
            4 => 'Five'
        ];

        $text .= '<div class="ac">';

        if (!empty($arr['CHILDREN'])) {
            if ($count == 0) {
                $text .= '<div class="ac-header sidebar__accordion-header">
                                 <div class="ac-trigger sidebar__accordion-trigger">
                                     <div class="sidebar__accordion-icon icon-arrow"></div>
                                     <div class="sidebar__accordion-text">' . $arr['NAME'] . '</div>
                                 </div>
                             </div>';
            } elseif ($count > 0) {
                $text .= '<div class="ac-header sidebar__accordion-header">
                                <div class="ac-trigger sidebar__accordion' . $classes[$count] . '-trigger">
                                    <div class="sidebar__accordion-icon' . $classes[$count] . ' icon-arrow"></div>
                                    <div class="sidebar__accordion-subtext">' . $arr['NAME'] . '</div>
                                </div>
                            </div>
                            <div class="ac-panel panel' . $classes[$count] . '">';
            }
            $count++;
            foreach ($arr['CHILDREN'] as $item) {
                self::getStructure($item, $text, $count);
                $text .= '</div>';
            }
            $count--;
        } else {
            $text .= '<div class="ac-panel">
                        <a href="/wiki/news/' . $arr['ID'] . '/" class="panel' . $classes[$count] . '__text">' . $arr['NAME'] . '</a>
                      </div>';
        }
    }



    public function getIblockSections($iblockId, $sectionId = false)
    {
        $result = [];
        $filter = ['IBLOCK_ID' => $iblockId, 'SECTION_ID' => $sectionId];
        $sort = ['SORT' => 'ASC'];
        $sections = \CIBlockSection::GetList(
            $sort,
            $filter,
            false,
            ['ID', 'NAME', 'IBLOCK_SECTION_ID']
        );

        while ($section = $sections->Fetch()) {
            $result[$section['ID']] = [
                'ID' => $section['ID'],
                'NAME' => $section['NAME'],
                'IBLOCK_SECTION_ID' => $section['IBLOCK_SECTION_ID'],
                'CHILDREN' => $this->getIblockSections($iblockId, $section['ID'])
            ];
        }

        return $result;
    }

    public function configureActions()
    {
        return [];
    }
}

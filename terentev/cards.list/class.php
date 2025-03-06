<?php

/** Класс компонента: Карточки в виде списка */

namespace Terentev;

use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Iblock\CIBlockSection;

class TerentevCardsListComponent extends \CBitrixComponent implements Controllerable
{
    public function executeComponent()
    {
        $this->arResult['IMG_PATH'] = '/local/components/terentev/cards.list/templates/.default/img';

        $iblockId = 173;
        $sectionId = 0;
        $userId = Users::getId();
        // $userId = 1755;

        // Получаем данные через универсальный метод
        $sections = DataProvider::getSectionsWithAccess($iblockId, $userId, $sectionId );

        if ($this->StartResultCache(3600, $userId)) {
            $this->arResult['DATA'] = [
                "ID" => $iblockId,
                "NAME" => "wiki",
                "CHILDREN" => $sections,
            ];

            $this->IncludeComponentTemplate();
        }
    }

    /**
     * Создание новой папки 
     *
     * @param string $name Название папки
     * @return AjaxJson
     */
    public function createFolderAction($name): AjaxJson
    {
        $iblockId = 173; // ID инфоблока

        $file = $_FILES['file'];

        // Создаем массив файла
        $fileArray = \CFile::MakeFileArray($file['tmp_name']);
        $fileArray['name'] = $file['name']; // Указываем имя файла для сохранения

        // Добавляем новый раздел в инфоблок
        $newSectionFields = [
            "IBLOCK_ID" => $iblockId,
            "NAME" => $name,
            "ACTIVE" => "Y",
            "PICTURE" => $fileArray, // Привязываем файл к полю PICTURE
            "IBLOCK_SECTION_ID" => 0 // Родительский раздел (в данном случае верхний уровень)
        ];

        $section = new \CIBlockSection;
        $newSectionId = $section->Add($newSectionFields);

        if ($newSectionId) {
            // Получаем путь к изображению
            $picturePath = \CFile::GetPath($fileArray['tmp_name']);

            return AjaxJson::createSuccess([
                "ID" => $newSectionId,
                "NAME" => $name,
                "PICTURE" => $picturePath
            ]);
        }
    }

    /**
     * Конфигурация доступных действий для API
     *
     * @return array
     */
    public function configureActions()
    {
        return [
            'createFolder' => [
                'prefilters' => [
                    new Csrf(),
                    new HttpMethod([HttpMethod::METHOD_POST])
                ]
            ],
        ];
    }
}

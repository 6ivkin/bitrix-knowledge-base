<?php

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Uri;

use Proxima\Service\Component\Simple;
use Proxima\Service\Component\GridHelper;

if(!Loader::includeModule('proxima.service')) {
    throw new Exception('Ошибка подключения модуля proxima.service');
}

/**
 * Class CITBServiceOrmAnnotation
 */
class CITBServiceOrmAnnotation extends Simple
{

    /**
     * @return mixed|void|null
     */
    public function executeComponent()
    {
        try {
            $folder = $_SERVER['DOCUMENT_ROOT'];
            $grid = new GridHelper('itb_hr_datacollector_list');
            $this->setGrid($grid);
            $grid->setColumns([
                [
                    'id' => 'ID',
                    'name' => 'Название модуля',
                    'sort' => false,
                    'default' => true,
                ]
            ]);

            // Список модулей в системе
            /** @var \Bitrix\Main\DB\Connection */
            $connection = Application::getInstance()->getConnection();
            //$filter = $grid->getFilterData();
            $sqlCond = "";
            $searchString = $grid->getFilterOptions()->getSearchString();
            if (!empty($searchString)) {
                $sqlCond = " where ID like '%" . strtr(quotemeta($searchString), ['"' => "", "'" => ""]) . "%'";
            }
            $dbRes = $connection->query("select * from b_module" . $sqlCond);

            while ($item = $dbRes->fetch()) {
                $grid->addRow(['data' => ['ID' => $item['ID']]]);
            }

        } catch (Exception $e) {
            $this->addErrorCompatible($e->getMessage());
        }
        $this->IncludeComponentTemplate();
    }

}

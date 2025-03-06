<?php

use Bitrix\Main\Application;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\CurrentUser;
//use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;


class CITBServiceOrmAnnotationController extends Controller
{
    /**
     * @throws LoaderException
     */
    protected function init()
    {
        parent::init();
    }

    /**
     * @return array
     */
    public function configureActions(): array
    {
        return [
            'executeCommand' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(['POST']),
                    new ActionFilter\Csrf(),
                ],
            ],
        ];
    }


    /**
     * @param array $ids
     * @return string
     */
    public function executeCommandAction(array $ids): string
    {
        $res = "";
        try {
            if (!CurrentUser::get()->isAdmin()) {
                throw new Exception('Нет доступа');
            }
            $folder = $_SERVER['DOCUMENT_ROOT'];

            //$res.= "<br>DEBUG ".print_r($ids, true); // fixme

            // Получение полного списка модулей
            /** @var \Bitrix\Main\DB\Connection */
            $connection = Application::getInstance()->getConnection();
            $dbRes = $connection->query("select * from b_module");
            $modulesList = [];
            while ($item = $dbRes->fetch()) {
                $modulesList[] = $item['ID'];
            }

            // Провека корректности имен модулей
            foreach ($ids as $k => $name) {
                if (!in_array($name, $modulesList))
                    throw new Exception('Ошибка! Неправильное имя модуля (' . $name . ')');
            }

            if (count($ids)) {
                // prepare
                $sList = implode(",", $ids);
                $sCmd = "php bitrix.php orm:annotate -v -m " . $sList;
                $res .= "<br>Команда: $sCmd";

                $output = null;
                $retVal = null;

                exec("cd $folder/bitrix && $sCmd", $output, $retVal);

                $res .= "<br>Команда выполнена (код: $retVal)<br>Результат:\n<br>";
                $res .= "<br><pre>" . implode("\n", $output) . "</pre>\n";

            }

        } catch (Exception $e) {
            $this->addError(new \Bitrix\Main\Error($e->getMessage()));
        }
        return $res;
    }

}
<?php

use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\UI\Buttons\JsCode;
use Proxima\Service\Component\Complex;
use Proxima\Service\Component\GridHelper;
use Proxima\Service\Update\Manager;

if (!Loader::includeModule('proxima.service')) {
    throw new Exception('Ошибка подключения модуля proxima.service');
}

class ServiceModuleList extends Complex
{
    /**
     * @return mixed|void|null
     */
    public function executeComponent()
    {
        try {
            $this->initRoute(
                [
                    'list' => '',
                    'check' => 'check/#ID#/',
                    'update' => 'update/#ID#/',
                ],
                'list'
            )->run();
            if($this->getRoute()->getAction() === $this->getRoute()->getDefaultAction()) {
                $grid = new GridHelper('itb_service_module_list');
                $this->setGrid($grid);
                $grid->setColumns([
                    [
                        'id' => 'ID',
                        'name' => 'ID',
                        'sort' => false,
                        'default' => true,
                    ],
                    [
                        'id' => 'TITLE',
                        'name' => 'Название',
                        'sort' => false,
                        'default' => true,
                    ],
                    [
                        'id' => 'DESCRIPTION',
                        'name' => 'Описание',
                        'sort' => false,
                        'default' => true,
                    ],
                    [
                        'id' => 'VERSION',
                        'name' => 'Версия',
                        'sort' => false,
                        'default' => true,
                    ],
                ]);

                if (!CurrentUser::get()->isAdmin()) {
                    throw new Exception('Доступ запрещен');
                }

                foreach (Manager::getModuleList() as $moduleId) {
                    $module = \CModule::CreateModuleObject($moduleId);
                    if ($module) {
                        $grid->addRow(
                            [
                                'data' => [
                                    'ID' => $module->MODULE_ID,
                                    'TITLE' => $module->MODULE_NAME,
                                    'DESCRIPTION' => $module->MODULE_DESCRIPTION,
                                    'VERSION' => $module->MODULE_VERSION . ' (' . $module->MODULE_VERSION_DATE . ')'
                                ],
                                'actions' => [
                                    [
                                        'text' => 'Проверка',
                                        'default' => true,
                                        'onclick' => (new JsCode('BX.SidePanel.Instance.open("' . $this->getRoute()->getUrl('check', ['ID' => $moduleId]) . '",
                                        {
                                            cacheable: false,
                                            width: 1400
                                        }
                                    );'))->getCode(),
                                    ],
                                    [
                                        'text' => 'Обновить',
                                        'default' => false,
                                        'onclick' => (new JsCode('updateModule("' . $moduleId . '", "' . $grid->getGridId() . '")'))->getCode(),
                                    ]
                                ]
                            ]
                        );
                    }
                }
            }
        } catch (Exception $e) {
            $this->addErrorCompatible($e->getMessage());
        }
        $this->includeComponentTemplate();
    }
}
<?php

use Bitrix\Main\Loader;
use Bitrix\UI\Buttons\JsCode;
use Proxima\Service\Component\Complex;
use Proxima\Service\Component\GridHelper;
use Proxima\Messages\Delete\Model\MemoryTable;

if(!Loader::includeModule('proxima.messages.delete')) {
    throw new Exception('Ошибка подключения модуля proxima.messages.delete');
}
if(!Loader::includeModule('proxima.service')) {
    throw new Exception('Ошибка подключения модуля proxima.service');
}

class CProximaMemoryList extends Complex
{
    /**
     * @return mixed|void|null
     */
    public function executeComponent()
    {
        try {
            $this->initRoute(
                [
                    'memory.list' => '',
                ],
                'memory.list'
            );
            $this->getRoute()->run();

            if($this->getRoute()->getAction() === $this->getRoute()->getDefaultAction()) {

                $grid = new GridHelper('proxima_memory_list');
                $this->setGrid($grid);
                $grid->setFilter([
                    [
                        'id' => 'USER_ID',
                        'name' => 'ID',
                        'type' => 'number',
                        'default' => true,
                    ],
                    [
                        'id' => 'FULL_NAME',
                        'name' => 'Пользователь',
                        'type' => 'string',
                        'default' => true,
                    ],
                    [
                        'id' => 'MEMORY_SIZE',
                        'name' => 'Занимаемая память',
                        'type' => 'string',
                        'default' => true,
                    ],
                ])->setColumns([
                    [
                        'id' => 'USER_ID',
                        'name' => 'ID',
                        'sort' => 'USER_ID',
                        'default' => true,
                    ],
                    [
                        'id' => 'FULL_NAME',
                        'name' => 'Пользователь',
                        'sort' => 'FULL_NAME',
                        'default' => true,
                    ],
                    [
                        'id' => 'MEMORY_SIZE',
                        'name' => 'Занимаемая память',
                        'sort' => 'MEMORY_SIZE',
                        'default' => true,
                    ]
                ]);

                $filter = $grid->getFilterData();
                $searchString = $grid->getFilterOptions()->getSearchString();
                if (!empty($searchString)) {
                    $filter['%=FULL_NAME'] = '%' . $searchString . '%';
                }

                MemoryTable::fillTable();

                $result = MemoryTable::getList(
                    [
                        'select' => ['*'],
                        'filter' => $filter,
                        'order' => $grid->getSort(),
                        'limit' => $grid->getNavigation()->getLimit(),
                        'offset' => $grid->getNavigation()->getOffset(),
                        'count_total' => true,
                    ]
                );

                while($user = $result->fetchObject()) {
                    $grid->addRow(
                        [
                            'data' => [
                                'USER_ID' => $user->getUserId(),
                                'FULL_NAME' => $user->getFullName(),
                                'MEMORY_SIZE' => $user->getMemorySize()
                            ],
                            'actions' => [
                                [
                                    'text' => 'Удалить сообщения',
                                    'default' => false,
                                    'onclick' => (new JsCode('deleteUserMessagesItem('.$user->getUserId().', "' . $grid->getGridId() . '")'))->getCode(),
                                ]
                            ]
                        ]
                    );
                }
                $grid->getNavigation()->setRecordCount($result->getCount());
            }
        } catch (Exception $e) {
            $this->addErrorCompatible($e->getMessage());
        }
        $this->IncludeComponentTemplate();
    }

    /**
     * Действие контроллера для удаления записи
     * @param int $id
     * @return bool
     */
    public function deleteUserMessagesAction(int $id): bool
    {
        try {
/*            $item = MemoryTable::getByPrimary($id)->fetchObject();
            if(!$item) {
                throw new Exception('Пользователь не найден');
            }*/
            $result = MemoryTable::deleteMessages($id);
            if(!$result->isSuccess()) {
                throw new Exception('Ошибка удаления: ' . implode('; ', $result->getErrorMessages()));
            }
        } catch (Exception $e) {
            $this->addError(
                new \Bitrix\Main\Error($e->getMessage())
            );
        }
        return true;
    }
}

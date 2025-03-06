<?php

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Loader;
use Bitrix\Main\Request;
use Proxima\Messages\Delete\Model\MemoryTable;
use Bitrix\Main\UserTable;
use Bitrix\Main\Entity\Result;

class ProximaMemoryListAjaxController extends Controller
{
    /**
     * @param Request|null $request
     * @throws Exception
     */
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        if(!Loader::includeModule('proxima.messages.delete')) {
            throw new Exception('Ошибка загрузки модуля proxima.messages.delete');
        }
    }

    /**
     * Действие контроллера для удаления записи
     * @param int $id
     * @return bool
     */
    public function deleteUserMessagesAction(int $id): bool
    {
        try {
            $item = UserTable::getByPrimary($id)->fetchObject();
            if(!$item) {
                throw new Exception('Пользователь не найден');
            }
            $result = MemoryTable::deleteMessages($id);
            // if(!$result->isSuccess(true)) {
            //     throw new Exception('Ошибка удаления: ' . implode('; ', $result->getErrorMessages()));
            // }
        } catch (Exception $e) {
            $this->addError(
                new \Bitrix\Main\Error($e->getMessage())
            );
        }
        return true;
    }
}
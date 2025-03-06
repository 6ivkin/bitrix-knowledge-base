<?php
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Loader;
use Bitrix\Main\Request;
use Proxima\Service\Update\Manager;

class ServiceModuleListAjaxController extends Controller
{
    /**
     * @param Request|null $request
     * @throws Exception
     */
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        if (!Loader::includeModule('proxima.service')) {
            throw new Exception('Модуль proxima.service не подключен');
        }
    }

    /**
     * @param string $moduleId
     * @return bool
     */
    public function updateAction(string $moduleId): bool
    {
        try {
            if (!\Bitrix\Main\Engine\CurrentUser::get()->isAdmin()) {
                throw new Exception('Нет доступа');
            }
            return Manager::update($moduleId);
        } catch (Exception $e) {
            $this->addError(new \Bitrix\Main\Error($e->getMessage()));
            return false;
        }
    }
}
<?php

use Bitrix\Main\Loader;
use Bitrix\UI\Buttons\JsCode;
use Proxima\Service\Component\Complex;
use Proxima\Service\Component\GridHelper;
use Proxima\Invoices\Module\Model\InvoicesTable;

if(!Loader::includeModule('proxima.invoices.module')) {
    throw new Exception('Ошибка подключения модуля proxima.invoices.module');
}
if(!Loader::includeModule('proxima.service')) {
    throw new Exception('Ошибка подключения модуля proxima.service');
}

class CProximaInvoicesList extends Complex
{
    /**
     * @return mixed|void|null
     */
    public function executeComponent()
    {
        try {
            $this->initRoute(
                [
                    'proxima.invoices.component' => '',
                    'proxima.invoices.sum' => 'edit',
                ],
                'proxima.invoices.component'
            );
            $this->getRoute()->run();

            if($this->getRoute()->getAction() === $this->getRoute()->getDefaultAction()) {

                $grid = new GridHelper('proxima_invoices_list');
                $this->setGrid($grid);
                $grid->setFilter([
                    [
                        'id' => 'ID',
                        'name' => 'ID',
                        'type' => 'number',
                        'default' => true,
                    ],
                    [
                        'id' => 'CONTRAGENT',
                        'name' => 'Наименование контрагента',
                        'type' => 'string',
                        'default' => true,
                    ],
                    [
                        'id' => 'NOMER',
                        'name' => 'Номер счета',
                        'type' => 'string',
                        'default' => true,
                    ],
                    [
                        'id' => 'SUMMA',
                        'name' => 'Сумма по счету',
                        'type' => 'number',
                        'default' => true,
                    ],
                    [
                        'id' => 'DATE',
                        'name' => 'Дата',
                        'type' => 'date',
                        'default' => true,
                    ],
                ])->setColumns([
                    [
                        'id' => 'ID',
                        'name' => 'ID',
                        'sort' => 'ID',
                        'default' => true,
                    ],
                    [
                        'id' => 'CONTRAGENT',
                        'name' => 'Наименование контрагента',
                        'sort' => 'CONTRAGENT',
                        'default' => true,
                    ],
                    [
                        'id' => 'NOMER',
                        'name' => 'Номер счета',
                        'sort' => 'NOMER',
                        'default' => true,
                    ],
                    [
                        'id' => 'SUMMA',
                        'name' => 'Сумма по счету',
                        'sort' => 'SUMMA',
                        'default' => true,
                    ],
                    [
                        'id' => 'DATE',
                        'name' => 'Дата',
                        'sort' => 'DATE',
                        'default' => true,
                    ],
                ]);

                $filter = $grid->getFilterData();
                $searchString = $grid->getFilterOptions()->getSearchString();
                if (!empty($searchString)) {
                    $filter['%=NOMER'] = '%' . $searchString . '%';
                }

                $result = InvoicesTable::getList(
                    [
                        'select' => ['*'],
                        'filter' => $filter,
                        'order' => $grid->getSort(),
                        'limit' => $grid->getNavigation()->getLimit(),
                        'offset' => $grid->getNavigation()->getOffset(),
                        'count_total' => true,
                    ]
                );

                while($invoice = $result->fetchObject()) {
                    $grid->addRow(
                        [
                            'data' => [
                                'ID' => $invoice->getId(),
                                'CONTRAGENT' => $invoice->getContragent(),
                                'NOMER' => $invoice->getNomer(),
                                'SUMMA' => $invoice->getSumma(),
                                'DATE' => $invoice->getDate(),
                            ],
                            'actions' => []
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
}

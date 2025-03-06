<?php

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\UI\Buttons\JsCode;
use Proxima\Service\Component\Complex;
use Proxima\Service\Component\GridHelper;
use Bitrix\Main\Web\Uri;
use Proxima\Service\Component\Simple;
use Proxima\Invoices\Module\Model\InvoicesTable;

if (!Loader::includeModule('proxima.invoices.module')) {
    throw new Exception('Ошибка подключения модуля proxima.invoices.module');
}
if (!Loader::includeModule('proxima.service')) {
    throw new Exception('Ошибка подключения модуля proxima.service');
}

class CProximaBurkovGameEdit extends Complex
{
    public function executeComponent()
    {
        try {
            $this->initRoute(
                [
                    'proxima.invoices.sum' => 'edit',
                ],
                'proxima.invoices.sum'
            );
            $this->getRoute()->run();

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $dateFrom = $_POST['DATE_FROM']; // Get the selected start date
                $dateTo = $_POST['DATE_TO'];     // Get the selected end date

                // Calculate the sum within the selected date range
                $sum = 0; // Initialize the sum

                $result = InvoicesTable::getList([
                    'select' => ['SUMMA'],
                    'filter' => [
                        '>=DATE' => $dateFrom,
                        '<=DATE' => $dateTo,
                    ],
                ]);

                while ($invoice = $result->fetchObject()) {
                    $sum += $invoice->getSumma();
                }

                // Pass the calculated sum to the template
                $this->arResult['SUM'] = $sum;
            }

            // ... (existing code)
        } catch (Exception $e) {
            $this->addErrorCompatible($e->getMessage());
        }
        $this->IncludeComponentTemplate();
    }

    /**
     * @return Game|null
     */
    public function getGame(): ?Game
    {
        return $this->game;
    }
}

<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\UI\Buttons\JsCode;
use Proxima\Service\Component\GridHelper;
use Proxima\Service\Component\Simple;

if (!Loader::includeModule('proxima.service')) {
    throw new Exception('Ошибка подключения модуля proxima.service');
}

/**
 * Class CITBServiceModuleCheck
 */
class CITBServiceModuleCheck extends Simple
{
    public string $moduleName = "";

    public array $compResult = [];
    public array $actResult = [];

    protected bool $debug = false;
    protected bool $showDiff = true;

    /**
     * @return void
     */
    public function executeComponent()
    {
        try {
            if (empty($this->arParams['HELPER']))
                throw new Exception('Некорректный вызов компонента');

            if (!CurrentUser::get()->isAdmin())
                throw new Exception('Нет доступа');

            $this->setRoute($this->arParams['HELPER']);
            $id = strval($this->getRoute()->getVariable('ID')); // sample: proxima.service
            $this->moduleName = $id;
            $docRoot = $_SERVER['DOCUMENT_ROOT']; //  /home/bitrix/www
            if (empty($id))
                throw new Exception('Некорректный параметр компонента');

            $cmpSrc = $docRoot . '/' . "local/modules/$id/install/components";
            $cmpDst = $docRoot . '/' . "local/components/proxima";

            if ($this->debug) echo "<br>Source " . $cmpSrc;
            if ($this->debug) echo "<br>Destin " . $cmpDst;
            if ($this->debug) echo "<br>Source " . $actSrc;
            if ($this->debug) echo "<br>Destin " . $actDst;


            $cmpSrcList = $this->getDirList($cmpSrc, $this->debug);
            $cmpDstList = $this->getDirList($cmpDst, $this->debug);
            $cmpResult = $this->compareDirs($cmpSrcList, $cmpDstList, $this->debug);
            if ($this->debug) echo "<br>RESULT <pre>" . print_r($cmpResult, true) . "</pre>";

            $actResult = [];
            try {
                $actSrcList = $this->getDirList($actSrc, $this->debug);
                $actDstList = $this->getDirList($actDst, $this->debug);
                $actResult = $this->compareDirs($actSrcList, $actDstList, $this->debug);
                if ($this->debug) echo "<br>RESULT <pre>" . print_r($actResult, true) . "</pre>";
            } catch (Exception $e) {
                $this->addErrorCompatible($e->getMessage());
            }

            $grid = new GridHelper('itb_service_component_list');
            $this->setGrid($grid);
            $grid->setColumns([
                [
                    'id' => 'ITEM',
                    'name' => 'Элемент',
                    'sort' => false,
                    'default' => true,
                ],
                [
                    'id' => 'TYPE',
                    'name' => 'Тип',
                    'sort' => false,
                    'default' => true,
                ],
                [
                    'id' => 'COMMENT',
                    'name' => 'Комментарий',
                    'sort' => false,
                    'default' => true,
                ],
                [
                    'id' => 'NEW_ITEMS',
                    'name' => 'Новые',
                    'sort' => false,
                    'default' => true,
                ],
                [
                    'id' => 'OLD_ITEMS',
                    'name' => 'Старые',
                    'sort' => false,
                    'default' => true,
                ],
                [
                    'id' => 'CHANGED_ITEMS',
                    'name' => 'Изменные',
                    'sort' => false,
                    'default' => true,
                ],
            ]);

            foreach ($cmpResult as $name => $item) {
                $grid->addRow([
                    'data' => [
                        'ITEM' => $name,
                        'TYPE' => 'Компонент',
                        'COMMENT' => $item['INFO'],
                        'NEW_ITEMS' => implode("<br>", $item['NEW']),
                        'OLD_ITEMS' => implode("<br>", $item['OLD']),
                        'CHANGED_ITEMS' => $this->showDiff ? $this->makeDiffLink($id, 'component', $name, $item['DIFF']) : implode("<br>", $item['DIFF']),
                    ],
                ]);
            }

            foreach ($actResult as $name => $item) {
                $grid->addRow([
                    'data' => [
                        'ITEM' => $name,
                        'TYPE' => 'Действие',
                        'COMMENT' => $item['INFO'],
                        'NEW_ITEMS' => implode("<br>", $item['NEW']),
                        'OLD_ITEMS' => implode("<br>", $item['OLD']),
                        //'CHANGED_ITEMS' => implode("<br>",$item['DIFF']),
                        'CHANGED_ITEMS' => $this->makeDiffLink($id, 'activity', $name, $item['DIFF']),
                    ],
                ]);
            }

        } catch (Exception $e) {
            $this->addErrorCompatible($e->getMessage());
            if ($this->debug) $this->addErrorCompatible("<pre>" . $e->getTraceAsString() . "</pre>");

        } catch (Error $e) {
            $this->addErrorCompatible($e->getMessage());
            if ($this->debug) $this->addErrorCompatible("<pre>" . $e->getTraceAsString() . "</pre>");
        }

        $this->IncludeComponentTemplate();

    }

    /**
     * @param $moduleId
     * @param $type
     * @param $subdir
     * @param $itemList
     * @return string
     */
    public function makeDiffLink($moduleId, $type, $subdir, $itemList): string
    {
        $resItems = [];
        foreach ($itemList as $item) {
            $resItems[] = '<a class="item-diff-view" href="#" data-params=\'' . json_encode([
                    'module' => $moduleId,
                    'type' => $type,
                    'subdir' => $subdir,
                    'item' => $item,
                ]) . '\'>' . $item . '</a>';
        }
        return implode("<br>", $resItems);
    }

    /**
     * @param $src
     * @param $dst
     * @param false $debug
     * @return array
     */
    public function compareDirs($src, $dst, $debug = false)
    {
        $res = [];
        if ($debug) echo "<br>compare dirs " . count($src) . ' ~ ' . count($dst);

        foreach ($src as $id => $srcItem) {
            $res[$id] = [
                'INFO' => '',
                'DIFF' => [],
                'NEW' => [],
                'OLD' => [],
            ];
            if (array_key_exists($id, $dst)) {
                $dstItem = $dst[$id];
                // check for new and changed items
                foreach ($srcItem['LIST'] as $k => $fileName) {
                    if (in_array($fileName, $dstItem['LIST'])) {
                        $hash1 = $srcItem['HASH'][$fileName];
                        $hash2 = $dstItem['HASH'][$fileName];

                        if ($hash1 != $hash2) {
                            $res[$id]['DIFF'][] = $fileName;
                        } else {
                            if ($debug) echo "<br>The same $fileName";
                        }
                    } else {
                        $res[$id]['NEW'][] = $fileName;
                    }
                }

                // check for old items
                foreach ($dstItem['LIST'] as $k => $fileName) {
                    if (!in_array($fileName, $srcItem['LIST'])) {
                        $res[$id]['OLD'][] = $fileName;
                    }
                }

            } else {
                $res[$id]['INFO'] = 'Новый каталог'; // 'NEW DIRECTORY'
            }
        }
        return $res;
    }

    /**
     * @param $basedir
     * @param false $debug
     * @return array
     * @throws Exception
     */
    public function getDirList($basedir, $debug = false)
    {
        $res = [];
        if ($debug) echo "<br>Check dir " . $basedir;

        $d = dir($basedir);
        if ($debug) echo "<br>DIR = " . print_r($d, true) . ' ~ ' . gettype($d);
        if (!$d)
            throw new Exception("Отсутствует каталог $basedir");

        while (false !== ($entry = $d->read())) {
            if ($entry != '.' && $entry != '..') {
                $entryPath = $basedir . '/' . $entry;
                if (is_dir($entryPath)) {
                    $subDirItems = $this::rdir($entryPath);

                    if (count($subDirItems)) {
                        $arHash = [];
                        foreach ($subDirItems as $id => $item) {
                            $internalName = str_replace($basedir . '/', '', $item);
                            $arHash[$internalName] = md5_file($item);
                            $subDirItems[$id] = $internalName;
                        }
                        $res[$entry] = [
                            'ITEM' => $entry,
                            'LIST' => $subDirItems,
                            'HASH' => $arHash,
                        ];
                    } else
                        if ($debug) echo "<br>Error, empty dir " . $basedir . ' / ' . $entry;
                } else {
                    if ($debug) echo "<br>Error, unexpected file " . $basedir . ' / ' . $entry;
                }
            }
        }
        return $res;
    }

    /**
     * @param $dir
     * @return array
     */
    public static function rdir($dir)
    {
        $array = array();
        $d = dir($dir);
        while (false !== ($entry = $d->read())) {
            if ($entry != '.' && $entry != '..') {
                $entry = $dir . '/' . $entry;

                if (is_file($entry)) {
                    $array[] = $entry;
                }

                if (is_dir($entry)) {
                    $subdirs = self::rdir($entry);
                    if ($subdirs)
                        $array = array_merge($array, $subdirs);
                    //else
                    //    $array[] = $entry;
                }
            }
        }
        $d->close();
        return $array;
    }

}

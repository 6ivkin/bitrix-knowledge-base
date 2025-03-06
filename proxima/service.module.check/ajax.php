<?php

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;

/**
 * Class CITBServiceModuleCheckController
 */
class CITBMainSaleClassificationAjaxController extends Controller
{
    /**
     * @param $params
     * @return string
     */
    public function getDiffAction($params = 'params'): string
    {
        $res = '';
        try {
            if (!Loader::includeModule('proxima.service')) {
                throw new Exception('Ошибка подключения модуля proxima.service');
            }

            if (!CurrentUser::get()->isAdmin())
                throw new Exception('Нет доступа');

            $docRoot = $_SERVER['DOCUMENT_ROOT']; //  /home/bitrix/www
            //$res .= "<br>PARAMS<pre>" . print_r($params, true) . "</pre>";
            $type = $params['type'];
            $module = $params['module'];
            $subdir = $params['subdir'];
            $item = $params['item'];
            if (empty($type) || empty($module) || empty($item))
                throw new Exception('Некорректный параметр');

            if ($type == 'component') {
                $srcRoot = $docRoot . '/' . 'local/modules/';
                $src = $srcRoot . $module . '/install/components/' . $item;
                $dstRoot = $docRoot . '/' . 'local/components/proxima/';
                $dst = $dstRoot . $item;
            } else
                throw new Exception('Некорректный тип');

            if (self::safePath($src, $srcRoot) && self::safePath($dst, $dstRoot)) {
                //$res.="<br>/usr/bin/diff -u $src $dst<br>";
                $output = null;
                $retval = null;
                exec("/usr/bin/diff -u $src $dst", $output, $retval);

                if ($retval == 1) {
                    $outputList = array_map(function ($s) {
                        return str_replace(['<', '>',], ['&lt;', '&gt;',], $s);
                    }, $output);
                    $res .= "<pre>" . implode("\n", $outputList) . "</pre>";
                } else
                    throw new Exception("Ошибка ($retval)");
            } else {
                //$res .= '<br>Некорректный путь';
                throw new Exception('Некорректный путь');
            }

        } catch (Exception $e) {
            $this->addError(new \Bitrix\Main\Error($e->getMessage()));
            $this->addError(new \Bitrix\Main\Error($e->getTraceAsString()));
        } catch (Error $e) {
            $this->addError(new \Bitrix\Main\Error($e->getMessage()));
            $this->addError(new \Bitrix\Main\Error($e->getTraceAsString()));
        }
        return $res;
    }

    /**
     * @param string $p
     * @param string $rootPath
     * @return bool
     */
    public static function safePath(string $p, string $rootPath)
    {
        return ($p == realpath($p)
            && ($rootPath == substr($p, 0, strlen($rootPath)))
            && is_dir($rootPath)
            && is_file($p)
        );
    }

}
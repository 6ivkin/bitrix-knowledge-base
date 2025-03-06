<?php

namespace Proxima\Messages\Delete;

use Bitrix\Main\UserTable;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class Main
{
    public static function delete(): void
    {
        global $DB;
        
        $month = Option::get('proxima.messages.delete', 'months');
        $user = Option::get('proxima.messages.delete', 'users');
        $switch = Option::get('proxima.messages.delete', 'switch_on');

        $user = explode(',', $user);


        $sqlQuery = 'DELETE
                    FROM b_im_message
                    WHERE date_create < now() - INTERVAL ' . $month . ' MONTH';

        if ($user[0] != '') {
            if ($switch == 'Y') {
                $sqlQuery .= ' AND author_id IN (' . $user[0];
                if (count($user) > 1) {
                    for ($i = 1; $i < count($user); $i++) {
                        $sqlQuery .= ", " . $user[$i];
                    }
                }
                $sqlQuery .= ')';
            } else {
                $sqlQuery .= ' AND author_id NOT IN (' . $user[0];
                if (count($user) > 1) {
                    for ($i = 1; $i < count($user); $i++) {
                        $sqlQuery .= ", " . $user[$i];
                    }
                }
                $sqlQuery .= ')';
            }
        }
        $DB->Query($sqlQuery);
        
        // добавить оповещение что все успешно пройденно 
        echo "Завершенно!";

        $sqlQuery = 'OPTIMIZE TABLE b_im_message';
        $DB->Query($sqlQuery);
                
    }

    // public static function agent() {
    //     Fedor\Orm\Main::delete();

    //     return "agent();";
    // }

}
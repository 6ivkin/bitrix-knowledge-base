<?
/**
 * Подключается при подключении модуля через \Bitrix\Main\Loader::includeModule('module.name');
 */

 function delete()
 {
    if (CModule::includeModule('proxima.messages.delete')){

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
        
        return "delete();";
    }
     
    return "delete();";

 }
<?php

namespace Proxima\Messages\Delete\Model;

use Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Data\DataManager;
use Proxima\Messages\Delete\Memory;
use Bitrix\Main\UserTable;
use Bitrix\Main\Entity;

class MemoryTable extends DataManager
{
    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return 'f_memory';
    }

    /**
     * @return string
     */
    public static function getObjectClass(): string
    {
        return Memory::class;
    }

    /**
     * @return array
     */
    public static function getMap(): array
    {
        return array(
            new Fields\IntegerField(
                'ID',
                array(
                    'primary' => true,
                    'autocomplite' => true
                )
            ),
            new Fields\IntegerField('USER_ID'),
            new Fields\StringField('FULL_NAME', array('required' => true)),
            new Fields\StringField('MEMORY_SIZE', array(
                    'default_value' => ' '
                )
            )
        );
    }

    /**
     * @param Entity\Event $event
     * @return Entity\EventResult
     */
//    public static function onBeforeAdd(Entity\Event $event): Entity\EventResult
//    {
//        $result = new Entity\EventResult;
//        $data = $event->getParameter("fields");
//
//        $memory = MemoryTable::getMemory($data['USER_ID']);
//        $result->modifyFields(array('MEMORY_SIZE' => $memory));
//
//        return $result;
//    }

    /**
     * @param int $user_id
     * @return void
     */
    public static function deleteMessages(int $user_id): void
    {
        global $DB;

        $sqlQuery = 'DELETE
                    FROM b_im_message
                    WHERE date_create < now() - INTERVAL 1 MONTH AND author_id = ' . $user_id;

        $DB->Query($sqlQuery);
    }

    /**
     * @return void
     */
    public static function fillTable(): void
    {
        global $DB;

        $sqlQuery = 'TRUNCATE TABLE f_memory';
        $DB->Query($sqlQuery);

        $users = UserTable::getList(array('filter' => array(array('==EXTERNAL_AUTH_ID' => null, '!%=XML_ID' => 'livechat%'))));
        while ($user = $users->fetchObject()) {
            if($user->getId() != 9){
                $memory = MemoryTable::getMemory($user->getId());
                MemoryTable::add(array(
                    'FULL_NAME' => $user->getLastName() . ' ' . $user->getName(),
                    'USER_ID' => $user->getId(),
                    'MEMORY_SIZE' => $memory,
                ));
            }
        }
    }

    /**
     * @param int $user_id
     * @return string
     */
    private static function getMemory(int $user_id): string
    {
        global $DB;

        $sqlResult = "SELECT message FROM b_im_message WHERE author_id = " . $user_id;
        $queryResult = $DB->Query($sqlResult);

        $size = 0;

        while ($res = $queryResult->fetch()) {
            $size += strlen($res['message']);
        }

        return number_format($size / 1048576, 2) . ' MB';
    }

}
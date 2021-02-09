<?php

namespace ItexPrice\ORM;

use Bitrix\Main\Entity;

class OrmTable extends Entity\DataManager{
    private $MODULE_ID = "iaprog_multiregion";
    public static function getTableName()
    {
        $ob = new OrmTable();
        return $ob->MODULE_ID . '_profile';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'primary' => true,
                'autocomplete' => true,
            )),
            new Entity\StringField('NAME', array(
                'required' => true,
            ))
        );
    }
}
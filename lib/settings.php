<?php

namespace ItexPrice\Settings;

use Bitrix\Main\Entity;

class SettingsTable extends Entity\DataManager{
    private $MODULE_ID = "iaprog_multiregion";

    public static function getTableName()
    {
        $ob = new SettingsTable();
        return $ob->MODULE_ID . '_profile_setings';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'primary' => true,
                'autocomplete' => true,
            )),
            new Entity\StringField('PROFILE_ID'),
            new Entity\StringField('NAME'),
            new Entity\StringField('VALUE'),
        );
    }
}
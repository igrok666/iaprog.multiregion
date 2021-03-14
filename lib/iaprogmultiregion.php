<?

namespace iaprogMultiregion;

$MODULE_ID = "iaprog.multiregion";
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $MODULE_ID . '/install/index.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $MODULE_ID . '/lib/orm.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $MODULE_ID . '/lib/settings.php';

use Bitrix\Seo\Engine\Bitrix;
use ItexPrice\ORM\OrmTable;
use ItexPrice\Settings\SettingsTable;
use Bitrix\Main\Loader;
use CCatalogGroup;

if (\Bitrix\Main\Loader::includeModule('catalog')) {
    require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/catalog/mysql/cataloggroup.php");
}

Loader::includeModule('catalog');
Loader::includeModule('iblock');

class iaprogmultiregion
{
    public function RemoveProfile($ID)
    {
        OrmTable::delete($ID);
    }

    public function RemoveSettings($ID)
    {
        SettingsTable::delete($ID);
    }

    public function GetProfile()
    {
        $result = OrmTable::getList(array());
        return $result->FetchAll();
    }

    public function AddProfile($NAME)
    {
        $result = OrmTable::add(array('NAME' => $NAME));
        return $result->getId();
    }

    public function UpdateProfile($ID, $NAME)
    {
        OrmTable::update($ID, array('NAME' => $NAME));
    }

    public function GetProfileSettings($ID)
    {
        $result = SettingsTable::getList(array('filter' => array('PROFILE_ID' => $ID)));
        while ($ob = $result->fetch()) {
            $ob['VALUE'] = unserialize($ob['VALUE']);
            $arResult[$ob['NAME']] = $ob;
        }
        return $arResult;
    }

    public function UpdateProfileSettings($arFields)
    {
        $ID_PROFILE = $arFields['profile_id'];
        unset($arFields['profile_id']);
        unset($arFields['NAME']);
        $ob = new iaprogmultiregion();
        $oldProperty = $ob->GetProfileSettings($ID_PROFILE);
        foreach ($oldProperty as $key => $oneField) {
            $ID = $oneField['ID'];
            unset($oneField['ID']);
            $oneField['VALUE'] = serialize($arFields[$key]);
            unset($arFields[$key]);
            SettingsTable::update($ID, $oneField);
        }
        if (!empty($arFields)) {
            $ob->AddProfileSettings($ID_PROFILE, $arFields);
        }
    }

    public function AddProfileSettings($ID_PROFILE, $arFields)
    {
        if (!empty($arFields)) {
            foreach ($arFields as $key => $oneField) {
                SettingsTable::add([
                    'PROFILE_ID' => $ID_PROFILE,
                    'NAME' => $key,
                    'VALUE' => serialize($oneField),
                ]);
            }
        }
    }

}
<? defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use \Bitrix\Main\EventManager;
use \iaprogMultiregion\iaprogmultiregion;

class iaprog_multiregion extends CModule
{

    var $MODULE_ID = "iaprog.multiregion";
    var $MODULE_STR = "iaprog_multiregion";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;

    function iaprog_multiregion()
    {
        $arModuleVersion = array();

        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path . "/version.php");

        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }

        $this->MODULE_NAME = "Модуль мультирегиональности";
        $this->MODULE_DESCRIPTION = "После установки у вас появится возможность задавать настройки для конкретного домена";
        $this->PARTNER_NAME = "Александров Илья";
        $this->PARTNER_URI = "https:\\1c-dev.ru";
    }

    function InstallDB()
    {
        \Bitrix\Main\Application::getConnection()->queryExecute("CREATE TABLE IF NOT EXISTS `" . $this->MODULE_STR . "_profile` (`ID` int NOT NULL AUTO_INCREMENT, `NAME` varchar(255) NOT NULL, PRIMARY KEY(`ID`))");
        \Bitrix\Main\Application::getConnection()->queryExecute("CREATE TABLE IF NOT EXISTS `" . $this->MODULE_STR . "_profile_setings` (`ID` int NOT NULL AUTO_INCREMENT, `PROFILE_ID` int NOT NULL, `NAME` varchar(255) NOT NULL, `VALUE` varchar(255) NOT NULL, PRIMARY KEY(`ID`))");
    }

    function UnInstallDB()
    {
        \Bitrix\Main\Application::getConnection()->queryExecute("DROP TABLE IF EXISTS " . $this->MODULE_STR . "_profile");
        \Bitrix\Main\Application::getConnection()->queryExecute("DROP TABLE IF EXISTS " . $this->MODULE_STR . "_profile_setings");
    }

    function InstallFiles()
    {
        return true;
    }

    function UnInstallFiles()
    {
        return true;
    }

    function InstallEvents()
    {
        EventManager::getInstance()->registerEventHandler('main', "OnEndBufferContent", $this->MODULE_ID, "iaprog_multiregion", "OnEndBufferContentHandler");
//        RegisterModuleDependences("main", "OnEndBufferContent", $this->MODULE_ID, "iaprogmultiregion", "OnEndBufferContentHandler");
    }

    function UnInstallEvents()
    {
        EventManager::getInstance()->unRegisterEventHandler('main', "OnEndBufferContent", $this->MODULE_ID, "iaprog_multiregion", "OnEndBufferContentHandler");
//        UnRegisterModuleDependences("main", "OnEndBufferContent", $this->MODULE_ID, "iaprogmultiregion", "OnEndBufferContentHandler");
    }

    public static function OnEndBufferContentHandler(&$content)
    {
        if (stripos($_SERVER['REQUEST_URI'], '/bitrix/admin') === false) {
            $arProfile = iaprogmultiregion::GetProfile();

            foreach ($arProfile as $oneProfile) {
                if ($oneProfile['NAME'] == $_SERVER['HTTP_HOST']) {
                    $arProfileSettings = iaprogmultiregion::GetProfileSettings($oneProfile["ID"]);
                }
            }
            foreach ($arProfileSettings['REGION_TAG']['VALUE'] as $key => $TAG) {
                $content = str_replace($TAG, $arProfileSettings['REGION_TAG_VALUE']['VALUE'][$key], $content);
            }
        }
    }

    function DoInstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;
        $this->InstallFiles();
        $this->InstallDB();
        $this->InstallEvents();
        RegisterModule($this->MODULE_ID);
        $APPLICATION->IncludeAdminFile("Установка модуля " . $this->MODULE_ID, $DOCUMENT_ROOT . "/local/modules/" . $this->MODULE_ID . "/install/step.php");
    }

    function DoUninstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;
        $this->UnInstallFiles();
        $this->UnInstallDB();
        $this->UnInstallEvents();
        UnRegisterModule($this->MODULE_ID);
        $APPLICATION->IncludeAdminFile("Деинсталляция модуля " . $this->MODULE_ID, $DOCUMENT_ROOT . "/local/modules/" . $this->MODULE_ID . "/install/unstep.php");
    }
}

?>
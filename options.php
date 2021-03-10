<?
$MODULE_ID = "iaprog.multiregion";
$MODULE_STR = "iaprog_multiregion";
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $MODULE_ID . '/lib/ItexPrice.php';

use ItexPrice\Main;
/*test*/
$arProfile = Main::GetProfile();
if ($_GET['save'] == 'Y' && !empty($_POST['profile_id']) && $_POST['Apply'] == 'Сохранить') {
    unset($_POST['Apply']);
    $_GET["PROFILE"] = $_POST['profile_id'];
    if ($_POST['profile_id'] !== 'create') { /* Созранение изменений настроек профиля */
        if ($_POST['DELL_DOMAIN'] == 'Y'){
            Main::RemoveProfile($_POST['profile_id']);
            $AllProfileSettings = Main::GetProfileSettings($_POST['profile_id']);
            foreach ($AllProfileSettings as $oneSettings){
                Main::RemoveSettings($oneSettings['ID']);
            }
            LocalRedirect('/bitrix/admin/settings.php?mid=' . $MODULE_ID . '&lang=ru');
        }
        Main::UpdateProfileSettings($_POST);
        Main::UpdateProfile($_POST['profile_id'], $_POST['NAME']);
        LocalRedirect('/bitrix/admin/settings.php?mid=' . $MODULE_ID . '&lang=ru&PROFILE=' . $_POST['profile_id']);
    } else { /* Сохранение нового профиля */
        $ID = Main::AddProfile($_POST['NAME']);
        unset($_POST['profile_id']);
        unset($_POST['NAME']);
        Main::AddProfileSettings($ID, $_POST);
        LocalRedirect('/bitrix/admin/settings.php?mid=' . $MODULE_ID . '&lang=ru&PROFILE=' . $ID);
    }
}
?>
<script src="/local/modules/<?= $MODULE_ID ?>/lib/jquery-3.5.1.min.js"></script>
<link rel="stylesheet" href="/local/modules/<?= $MODULE_ID ?>/lib/style.css">

<form name="itex_price_list_options" method="POST"
      action="/bitrix/admin/settings.php?mid=<?= $MODULE_ID ?>&lang=ru&save=Y">
    <div class="adm-detail-block" id="tabControl_layout">
        <div class="adm-detail-tabs-block" id="tabControl_tabs">
                        <span id="tab_cont_edit1"
                              class="adm-detail-tab adm-detail-tab-active" onclick="tabControl.SelectTab('edit1');">Настройки</span>

            <div class="adm-detail-title-setting" onclick="tabControl.ToggleTabs();"
                 title="Развернуть все вкладки на одну страницу" id="tabControl_expand_link"><span
                        class="adm-detail-title-setting-btn adm-detail-title-expand"></span></div>
            <div onclick="tabControl.ToggleFix('top')" class="adm-detail-pin-btn-tabs"
                 title="Открепить панель"></div>
        </div>
        <div class="adm-detail-content-wrap">
            <div class="adm-detail-content" id="edit1">
                <div class="adm-detail-title">Настройка параметров модуля</div>
                <div class="adm-detail-content-item-block">
                    <table class="adm-detail-content-table edit-table" id="edit1_edit_table">
                        <tbody>
                        <tr class="heading">
                            <td colspan="2"><b>Выбор домена</b></td>
                        </tr>
                        <tr>
                            <td class="adm-detail-content-cell-l">Домен:</td>
                            <td class="adm-detail-content-cell-r">
                                <script>
                                    function test() {
                                        var url = new URL(document.location.href.replace('#authorize', ''));
                                        url.searchParams.delete('PROFILE');
                                        url.searchParams.delete('save');
                                        document.location.href = url.href + '&PROFILE=' + $('#profile_id').val();
                                    }
                                </script>
                                <select name="profile_id" id="profile_id" class="typeselect" onchange="test();">
                                    <option value="">Выберите домен</option>

                                    <? foreach ($arProfile as $oneProfile) { ?>
                                        <option value="<?= $oneProfile['ID'] ?>"
                                            <? if ($_GET["PROFILE"] == $oneProfile['ID']) {
                                                $CURRENT_PROFILE_NAME = $oneProfile['NAME'];
                                                ?> selected <? } ?>><?= $oneProfile['NAME'] ?>
                                        </option>
                                    <? } ?>
                                    <option value="create" <? if ($_GET["PROFILE"] == "create") { ?> selected <? } ?>>
                                        Добавить новый домен
                                    </option>
                                </select></td>
                        </tr>
                        <? if (!empty($_GET["PROFILE"])) { ?>
                            <tr class="heading">
                                <td colspan="2"><b>Настройки домена</b></td>
                            </tr>
                            <tr>
                                <td class="adm-detail-content-cell-l">
                                    Домен:
                                </td>
                                <td class="adm-detail-content-cell-r">
                                    <input type="text" name="NAME" value="<?= $CURRENT_PROFILE_NAME ?>">
                                </td>
                            </tr>
                            <?
                            $arProfileSettings = Main::GetProfileSettings($_GET["PROFILE"]);
                            if (!empty($CURRENT_PROFILE_NAME)) {
                                ?>
                                <tr>
                                    <td class="adm-detail-content-cell-l">Удалить?</td>
                                    <td class="adm-detail-content-cell-r">
                                        <input type="checkbox" id="DELL_DOMAIN" name="DELL_DOMAIN" value="Y" class="adm-designed-checkbox">
                                        <label class="adm-designed-checkbox-label" for="DELL_DOMAIN"
                                               title=""></label>
                                    </td>
                                </tr>
                                <tr class="heading">
                                    <td colspan="2"><b>Шапка</b></td>
                                </tr>
                                <tr>
                                    <td class="adm-detail-content-cell-l">Телефон 1</td>
                                    <td class="adm-detail-content-cell-r">
                                        <input type="text" name="PHONE" value="<?= $arProfileSettings['PHONE']['VALUE'] ?>" class="adm-designed-input">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="adm-detail-content-cell-l">Телефон 2</td>
                                    <td class="adm-detail-content-cell-r">
                                        <input type="text" name="PHONE_2" value="<?= $arProfileSettings['PHONE_2']['VALUE'] ?>" class="adm-designed-input">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="adm-detail-content-cell-l">Whatsap/Viber</td>
                                    <td class="adm-detail-content-cell-r">
                                        <input type="text" name="WHATSAP_VIBER" value="<?= $arProfileSettings['WHATSAP_VIBER']['VALUE'] ?>" class="adm-designed-input">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="adm-detail-content-cell-l">Email</td>
                                    <td class="adm-detail-content-cell-r">
                                        <input type="text" name="EMAIL" value="<?= $arProfileSettings['EMAIL']['VALUE'] ?>" class="adm-designed-input">
                                    </td>
                                </tr>
                            <? } ?>
                        <? } ?>
                        </tbody>
                    </table>
                </div>
                <div class="adm-detail-content-btns">
                    <input type="submit" name="Apply" value="Сохранить"
                           title="Сохранить изменения и остаться в форме"
                           class="adm-btn-save">
                </div>
            </div>
        </div>
    </div>
</form>
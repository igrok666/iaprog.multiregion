<?
$MODULE_ID = "iaprog.multiregion";
$MODULE_STR = "iaprog_multiregion";
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $MODULE_ID . '/lib/iaprogmultiregion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $MODULE_ID . '/lib/functions/AdminFields.php';

use iaprogMultiregion\iaprogmultiregion;

$arProfile = iaprogmultiregion::GetProfile();
if ($_GET['save'] == 'Y' && !empty($_POST['profile_id']) && $_POST['Apply'] == 'Сохранить') {
    unset($_POST['Apply']);
    $_GET["PROFILE"] = $_POST['profile_id'];
    if ($_POST['profile_id'] !== 'create') { /* Созранение изменений настроек профиля */
        if ($_POST['DELL_DOMAIN'] == 'Y') {
            iaprogmultiregion::RemoveProfile($_POST['profile_id']);
            $AllProfileSettings = iaprogmultiregion::GetProfileSettings($_POST['profile_id']);
            foreach ($AllProfileSettings as $oneSettings) {
                iaprogmultiregion::RemoveSettings($oneSettings['ID']);
            }
            LocalRedirect('/bitrix/admin/settings.php?mid=' . $MODULE_ID . '&lang=ru');
        }
        iaprogmultiregion::UpdateProfileSettings($_POST);
        iaprogmultiregion::UpdateProfile($_POST['profile_id'], $_POST['NAME']);
        LocalRedirect('/bitrix/admin/settings.php?mid=' . $MODULE_ID . '&lang=ru&PROFILE=' . $_POST['profile_id']);
    } else { /* Сохранение нового профиля */
        $ID = iaprogmultiregion::AddProfile($_POST['NAME']);
        unset($_POST['profile_id']);
        unset($_POST['NAME']);
        iaprogmultiregion::AddProfileSettings($ID, $_POST);
        LocalRedirect('/bitrix/admin/settings.php?mid=' . $MODULE_ID . '&lang=ru&PROFILE=' . $ID);
    }
}
?>
<script src="/local/modules/<?= $MODULE_ID ?>/lib/jquery-3.5.1.min.js"></script>
<link rel="stylesheet" href="/local/modules/<?= $MODULE_ID ?>/lib/style.css">

<form name="itex_price_list_options" method="POST"
      action="/bitrix/admin/settings.php?mid=<?= $MODULE_ID ?>&lang=ru&save=Y" autocomplete="off">
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
                        <? AdminField('head', 'Выбор домена') ?>
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
                            <? $arProfileSettings = iaprogmultiregion::GetProfileSettings($_GET["PROFILE"]); ?>
                            <? AdminField('head', 'Настройки домена') ?>
                            <? AdminField('input', array('TEXT' => 'Домен', 'NAME' => 'NAME', 'VALUE' => $arProfileSettings['NAME']['VALUE'] ? $arProfileSettings['NAME']['VALUE'] : $CURRENT_PROFILE_NAME)) ?>

                            <?

                            if (!empty($CURRENT_PROFILE_NAME)) {
                                ?>
                                <? AdminField('checkbox', array('TEXT' => 'Удалить домен?','NAME' => 'DELL_DOMAIN', 'VALUE' => $arProfileSettings['DELL_DOMAIN']['VALUE']))?>
                                <tr>
                                    <td class="adm-detail-content-cell-l">Удалить?</td>
                                    <td class="adm-detail-content-cell-r">
                                        <input type="checkbox" id="DELL_DOMAIN" name="DELL_DOMAIN" value="Y"
                                               class="adm-designed-checkbox">
                                        <label class="adm-designed-checkbox-label" for="DELL_DOMAIN"
                                               title=""></label>
                                    </td>
                                </tr>
                                <? AdminField('input', array('TEXT' => 'Телефон 1', 'NAME' => 'PHONE_1', 'VALUE' => $arProfileSettings['PHONE_1']['VALUE'] ? $arProfileSettings['PHONE_1']['VALUE'] : '')) ?>
                                <? AdminField('input', array('TEXT' => 'Телефон 2', 'NAME' => 'PHONE_2', 'VALUE' => $arProfileSettings['PHONE_2']['VALUE'] ? $arProfileSettings['PHONE_2']['VALUE'] : '')) ?>
                                <? AdminField('input', array('TEXT' => 'Whatsap/Viber', 'NAME' => 'WHATSAP_VIBER', 'VALUE' => $arProfileSettings['WHATSAP_VIBER']['VALUE'] ? $arProfileSettings['WHATSAP_VIBER']['VALUE'] : '')) ?>
                                <? AdminField('input', array('TEXT' => 'Email', 'NAME' => 'EMAIL', 'VALUE' => $arProfileSettings['EMAIL']['VALUE'] ? $arProfileSettings['EMAIL']['VALUE'] : '')) ?>
                                <? AdminField('input', array('TEXT' => 'Адрес', 'NAME' => 'ADDRESS', 'VALUE' => $arProfileSettings['ADDRESS']['VALUE'] ? $arProfileSettings['ADDRESS']['VALUE'] : '')) ?>
                                <tr>
                                    <td>
                                        <table>
                                            <? AdminField('multiinput', array('TEXT' => '#TAG_NAME#', 'NAME' => 'REGION_TAG', 'VALUE' => $arProfileSettings['REGION_TAG']['VALUE'])) ?>
                                        </table>
                                    </td>
                                    <td>
                                        <table>
                                            <? AdminField('multiinput', array('TEXT' => 'Значение тэга', 'NAME' => 'REGION_TAG_VALUE', 'VALUE' => $arProfileSettings['REGION_TAG_VALUE']['VALUE'])) ?>
                                        </table>
                                    </td>
                                </tr>

                                <!--                                --><? // AdminField('select', array('TEXT' => 'Тип шапки', 'NAME' => 'TYPE_HEADER', 'VALUE' => $ProfileSettings['TYPE_HEADER']['VALUE'], 'DEFAULT_VALUES' => array(1 => '1', 2 => '2'))) ?>
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
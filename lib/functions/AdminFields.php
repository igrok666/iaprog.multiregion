<?php

function AdminField($type, $array, $ProfileSettings = false)
{
    switch ($type) {
        case 'head':
            ?>
            <tr class="heading">
                <td colspan="2"><?= $array ?></td>
            </tr>
            <?
            break;
        case 'color':
            ?>
            <tr data-optioncode="THEME_SWITCHER">
                <td class="adm-detail-content-cell-l" width="50%">
                    <label for="<?= $array['NAME'] ?>"><?= $array['TEXT'] ?></label>
                </td>
                <td width="50%" class="adm-detail-content-cell-r">
                    <input type="text" id="<?= $array['NAME'] ?>" name="<?= $array['NAME'] ?>" class="js_politra"
                           value="<?= $array['VALUE'] ?>">
                </td>
            </tr>
            <?
            break;
        case 'file':
            ?>
            <tr data-optioncode="THEME_SWITCHER">
                <td class="adm-detail-content-cell-l" width="50%">
                    <label><?= $array['TEXT'] ?></label>
                </td>
                <td width="50%" class="adm-detail-content-cell-r">
                    <img src="<?= CFile::GetPath($array['VALUE']) ?>">
                    <?= CFile::InputFile($array['NAME'], 20, $array['VALUE']); ?>
                </td>
            </tr>
            <?
            break;
        case 'input':
            ?>
            <tr data-optioncode="THEME_SWITCHER">
                <td class="adm-detail-content-cell-l" width="50%">
                    <label><?= $array['TEXT'] ?></label>
                </td>
                <td width="50%" class="adm-detail-content-cell-r">
                    <input type="text" value="<?= $array['VALUE'] ?>" name="<?= $array['NAME'] ?>">
                </td>
            </tr>
            <?
            break;
        case 'multiinput':
            ?>
            <tr data-optioncode="THEME_SWITCHER">
                <td class="adm-detail-content-cell-l" width="50%">
                    <label><?= $array['TEXT'] ?></label>
                </td>
                <td width="50%" class="adm-detail-content-cell-r">
                    <? if (!empty($array['VALUE'])) { ?>
                        <? foreach ($array['VALUE'] as $keyValue => $OneValue) { ?>
                            <? if (!empty($OneValue)) { ?>
                                <input type="text" value="<?= $OneValue ?>"
                                       name="<?= $array['NAME'] ?>[<?= $keyValue ?>]"><br><br>
                            <? } ?>
                        <? } ?>
                        <input type="text" value="" name="<?= $array['NAME'] ?>[<?= $keyValue + 1 ?>]">
                    <? } else { ?>
                        <input type="text" value="" name="<?= $array['NAME'] ?>[0]">
                    <? } ?>
                </td>
            </tr>
            <?
            break;
        case 'select':
            ?>
            <tr data-optioncode="THEME_SWITCHER">
                <td class="adm-detail-content-cell-l" width="50%">
                    <label><?= $array['TEXT'] ?></label>
                </td>
                <td width="50%" class="adm-detail-content-cell-r">
                    <select name="<?= $array['NAME'] ?>" class="typeselect">
                        <? foreach ($array['DEFAULT_VALUES'] as $key => $Onevalue) { ?>
                            <option value="<?= $key ?>" <?= $array['VALUE'] == $key ? 'selected' : '' ?>>
                                <?= $Onevalue ?>
                            </option>
                        <?
                        } ?>
                    </select>
                </td>
            </tr>
            <?
            break;
        case 'checkbox':
            ?>
            <tr data-optioncode="THEME_SWITCHER">
                <td class="adm-detail-content-cell-l" width="50%">
                    <label for="<?= $array['NAME'] ?>"><?= $array['TEXT'] ?></label>
                </td>
                <td class="adm-detail-content-cell-r">
                    <input type="checkbox" id="<?= $array['NAME'] ?>" name="<?= $array['NAME'] ?>"
                        <?= $array['VALUE'] == 'on' ? 'checked' : '' ?>
                           class="adm-designed-checkbox">
                    <label class="adm-designed-checkbox-label" for="<?= $array['NAME'] ?>"></label>
                </td>
            </tr>
            <?
            break;
        case 'superBlock':
            ?>
            <tr data-optioncode="THEME_SWITCHER">
                <td class="adm-detail-content-cell-c" colspan="2" style="text-align: center">
                    <?= $array['TEXT'] ?>:
                </td>
            </tr>
            <tr data-optioncode="THEME_SWITCHER">
                <td class="adm-detail-content-cell-c" colspan="2">
                    <ul id="MAIN_PAGE" class="cs_sortable js_sortable">
                        <?
                        $CUSTOM_MAIN_BLOCK = 1;
                        $Replace = $array['DEFAULT_VALUE'];

                        foreach (explode(',', $array['VALUE']) as $oneBlock) {
                            ?>
                            <?
                            if (!empty($ProfileSettings[$oneBlock]) || !empty($Replace[$oneBlock])) { ?>
                            <li class="ui-state-default cs_sort_item js_sort_item"
                                data-value="<?= $oneBlock ?>">
                                <?= $Replace[$oneBlock] ?>
                                <? unset($Replace[$oneBlock]) ?>
                                <span class="cs_sort_delete js_sort_delete">x</span>
                                <?
                                if (strstr($oneBlock, 'CUSTOM_MAIN_BLOCK_') && !strstr($oneBlock, '_SETTINGS')) {
                                    ?>
                                    <?= $ProfileSettings[$oneBlock]['VALUE'] ?>
                                    <input type="hidden" name="<?= $oneBlock ?>"
                                           value="<?= $ProfileSettings[$oneBlock]['VALUE'] ?>">
                                    <input type="hidden"
                                           value="<?= $ProfileSettings[$oneBlock . "_SETTINGS"]['VALUE'] ?>"
                                           name="<?= $oneBlock ?>_SETTINGS">
                                    <span class="js_settings_custom_block cs_settings_custom_block"
                                          data-id="<?= $oneBlock ?>"
                                          data-name="<?= $ProfileSettings[$oneBlock]['VALUE'] ?>">Настроить</span>
                                    <span class="js_remove_custom_block cs_remove_custom_block"
                                          data-id="<?= $oneBlock ?>">Удалить</span></li>
                                    <?
                                } else {
                                    ?>
                                    </li>
                                <? }
                            }
                        }
                        foreach ($Replace as $keyBlock => $oneBlock) { ?>
                            <li class="ui-state-default cs_sort_item js_sort_item disabled"
                                data-value="<?= $keyBlock ?>">
                                <?= $oneBlock ?>
                                <span class="cs_sort_delete js_sort_delete">x</span>
                            </li>
                        <? } ?>
                        <?
                        foreach ($ProfileSettings as $oneSettings) {
                            if (strstr($oneSettings['NAME'], 'CUSTOM_MAIN_BLOCK_') && !strstr($oneSettings['NAME'], '_SETTINGS')) {
                                $NUMB = str_replace('CUSTOM_MAIN_BLOCK_', '', $oneSettings['NAME']);
                                if ((int)$NUMB >= $CUSTOM_MAIN_BLOCK) {
                                    $CUSTOM_MAIN_BLOCK = (int)$NUMB + 1;
                                }
                            }
                        }
                        $i = 1;
                        while ($i <= $CUSTOM_MAIN_BLOCK - 1) {
                            if (strstr($ProfileSettings['MAIN_PAGE']['VALUE'], 'CUSTOM_MAIN_BLOCK_' . $i) === false) {
                                if (!empty($ProfileSettings["CUSTOM_MAIN_BLOCK_" . $i])) {
                                    ?>
                                    <li class="ui-state-default cs_sort_item js_sort_item disabled custom_main_block"
                                        data-value="CUSTOM_MAIN_BLOCK_<?= $i ?>">
                                        <?= $ProfileSettings["CUSTOM_MAIN_BLOCK_" . $i]['VALUE'] ?>
                                        <input type="hidden" name="CUSTOM_MAIN_BLOCK_<?= $i ?>"
                                               value="<?= $ProfileSettings["CUSTOM_MAIN_BLOCK_" . $i]['VALUE'] ?>">
                                        <input type="hidden"
                                               value="<?= $ProfileSettings["CUSTOM_MAIN_BLOCK_" . $i . "_SETTINGS"]['VALUE'] ?>"
                                               name="CUSTOM_MAIN_BLOCK_<?= $i ?>_SETTINGS">
                                        <span class="cs_sort_delete js_sort_delete">x</span>
                                        <span class="js_settings_custom_block cs_settings_custom_block"
                                              data-id="CUSTOM_MAIN_BLOCK_<?= $i ?>"
                                              data-name="<?= $ProfileSettings["CUSTOM_MAIN_BLOCK_" . $i]['VALUE'] ?>">Настроить</span>
                                        <span class="js_remove_custom_block cs_remove_custom_block"
                                              data-id="CUSTOM_MAIN_BLOCK_<?= $i ?>">Удалить</span>
                                    </li>
                                    </li>
                                    <?
                                }
                            }
                            $i++;
                        } ?>
                        <li class="ui-state-default cs_sort_item js_sort_item disabled custom_main_block new"
                            data-value="CUSTOM_MAIN_BLOCK_<?= $CUSTOM_MAIN_BLOCK ?>">
                            <input type="text" disabled class="custom_main"
                                   value="Название кастомного блока <?= $CUSTOM_MAIN_BLOCK ?>"
                                   style="width: 90%"
                                   name="CUSTOM_MAIN_BLOCK_<?= $CUSTOM_MAIN_BLOCK ?>">
                            <input type="hidden" class="settings" value=""
                                   name="CUSTOM_MAIN_BLOCK_<?= $CUSTOM_MAIN_BLOCK ?>_SETTINGS"
                                   disabled>
                            <span class="cs_sort_delete js_sort_delete">x</span>
                            <span class="js_settings_custom_block cs_settings_custom_block"
                                  data-id="CUSTOM_MAIN_BLOCK_<?= $CUSTOM_MAIN_BLOCK ?>"
                                  data-name="">Настроить</span>
                        </li>
                    </ul>

                    <input type="hidden" name="MAIN_PAGE"
                           value="<?= $ProfileSettings['MAIN_PAGE']['VALUE'] ?>">
                </td>
            </tr>
            <?
            break;

    }
}
<? include($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/modules/itex.price.list/lib/ItexPrice.php';

use ItexPrice\Main;

if ($_POST['AJAX']) {
    if ($_POST['CREATE_PRICE'] !== 'Y') { /* AJAX запрос на получение свойств инфоблока */
        $obProperty = \Bitrix\Iblock\PropertyTable::getlist(array('filter' => array('IBLOCK_ID' => $_POST['ID']), 'select' => array('ID', 'NAME')));
        while ($ob = $obProperty->fetch()) {
            $arProperty[] = $ob;
        }
        $arCURRENT = unserialize($_POST['CURRENT']);
        foreach ($arProperty as $oneProperty) {
            ?>
            <option value="<?= $oneProperty['ID'] ?>" <?= in_array($oneProperty['ID'], $arCURRENT) ? "selected" : '' ?>><?= $oneProperty['NAME'] ?></option>
        <? }
    } else { /* AJAX запрос на создание прайс листа */
        $arProfileSettings = Main::GetProfileSettings($_POST["ID"]); /* Получение свойств выбранного профиля выгрузки */
        $alf = ['', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
        $i = 1;
        $SORT = array();
        if ($_POST['PRIMER'] !== 'Y') {
            foreach ($arProfileSettings['MAIN_SORT']['VALUE'] as $keySORT => $valSort) {
                $SORT[$valSort] = $i;
                $i++;
            }
        } else {
            $_POST['SORT'] = explode(';', $_POST['SORT']);
            foreach ($_POST['SORT'] as $keySORT => $valSort) {
                if (!empty($valSort)) {
                    $SORT[$valSort] = $i;
                    $i++;
                }
            }
        }
        if (empty($_POST['DATA']) && empty($_POST['AJAX_SHAG_2'])) { /* Создание шапки файла */
            $arFIELDS = CIBlockParameters::GetFieldCode(GetMessage("IBLOCK_FIELD"), "DATA_SOURCE");
            foreach ($arFIELDS['VALUES'] as $keyField => $oneVal) {
                $tempFields['FIELD_' . $keyField] = $oneVal;
            }
            $arFIELDS['VALUES'] = $tempFields; /* получаем стандартные поля инфоблока */
            if ($_POST['PRIMER'] !== 'Y') {
                $obProperty = \Bitrix\Iblock\PropertyTable::getlist(array('filter' => array('IBLOCK_ID' => $arProfileSettings['IBLOCK_ID']['VALUE']), 'select' => array('ID', 'NAME', 'PROPERTY_TYPE')));
            } else {
                $obProperty = \Bitrix\Iblock\PropertyTable::getlist(array('filter' => array('IBLOCK_ID' => $_POST['IBLOCK_ID']), 'select' => array('ID', 'NAME')));
            }
            $arProperty = [];
            while ($ob = $obProperty->fetch()) { /*получаем свойства инфоблока с элементами */
                $arProperty['VALUES']['PROPERTY_' . $ob['ID']] = $ob['NAME'];
                $arProperty['PROPERTY_TYPE']['PROPERTY_' . $ob['ID']] = $ob['PROPERTY_TYPE'];
            }
            if ($arProfileSettings['TRADING_OFFERS']['VALUE'] == 'on' || $_POST['TRADING_OFFERS'] == 'on') { /* Если выгружаем торговые предложения то собираем список свойств ТП */
                if ($_POST['PRIMER'] !== 'Y') {
                    $obPropertyTO = \Bitrix\Iblock\PropertyTable::getlist(array('filter' => array('IBLOCK_ID' => $arProfileSettings['TRADING_OFFERS_IBLOCK_ID']['VALUE']), 'select' => array('ID', 'NAME')));
                } else {
                    $obPropertyTO = \Bitrix\Iblock\PropertyTable::getlist(array('filter' => array('IBLOCK_ID' => $_POST['TRADING_OFFERS_IBLOCK_ID']), 'select' => array('ID', 'NAME')));
                }
                $arPropertyTO = [];
                while ($ob = $obPropertyTO->fetch()) {
                    $arPropertyTO['VALUES']['PROPERTY_OFFERS_' . $ob['ID']] = $ob['NAME'];
                }
            }
            /* создание нового файла */
            if ($_POST['PRIMER'] !== 'Y') {
                $xls = new PHPExcel();
                $xls->getProperties()->setTitle("Прайс лист");
                $xls->setActiveSheetIndex(0);
                $sheet = $xls->getActiveSheet();
                $sheet->setTitle('Прайс лист');
            } else {
                $PRIMER = array();
            }
            $i = 0;
            /* Создание шапки файла (названия колонок) стандартные поля элемента */
            if ($_POST['PRIMER'] !== 'Y') {
                foreach ($arProfileSettings['FIELDS']['VALUE'] as $oneFields) {/* Перебор выбранных стандартных полей элемента */
                    $sheet = Main::SetHeaderColumn($alf, $SORT['FIELD_' . $oneFields], 'FIELD_' . $oneFields, $arFIELDS, $sheet);
                }
            } else {
                foreach ($_POST['FIELDS'] as $oneFields) {
                    $PRIMER[$alf[$SORT['FIELD_' . $oneFields]] . "1"] = $arFIELDS['VALUES']['FIELD_' . $oneFields];
                }
            }

            /* Создание шапки файла (названия колонок) свойства элемента */
            $arSelectProperty = [];
            if ($_POST['PRIMER'] !== 'Y') {
                foreach ($arProfileSettings['PROPERTIES']['VALUE'] as $oneProperty) {/* Перебор выбранных свойств элемента */
                    $sheet = Main::SetHeaderColumn($alf, $SORT['PROPERTY_' . $oneProperty], 'PROPERTY_' . $oneProperty, $arProperty, $sheet);
                    $arSelectProperty[] = $oneProperty;  /* добавление в выборку только нужных свойств */
                }
            } else {
                foreach ($_POST['PROPERTIES'] as $oneProperty) {/* Перебор выбранных свойств элемента */
                    $PRIMER[$alf[$SORT['PROPERTY_' . $oneProperty]] . "1"] = $arProperty['VALUES']['PROPERTY_' . $oneProperty];
                    $arSelectProperty[] = $oneProperty;
                }
            }
            /* Создание шапки файла (название колонок) цены элемента */
            if (\Bitrix\Main\Loader::includeModule('catalog')) {
                $dbPriceType = Main::GetPriceGroup();
                while ($arPriceType = $dbPriceType->Fetch()) {
                    if ($_POST['PRIMER'] !== 'Y') {
                        if (!empty($SORT['PRICE_' . $arPriceType['ID']])) {
                            $sheet = Main::SetHeaderColumn($alf, $SORT['PRICE_' . $arPriceType['ID']], 'PRICE_' . $arPriceType['ID'], array('VALUES' => array('PRICE_' . $arPriceType['ID'] => $arPriceType['NAME_LANG'])), $sheet);
                        }
                    } else {
                        if (!empty($SORT['PRICE_' . $arPriceType['ID']])) {
                            $PRIMER[$alf[$SORT['PRICE_' . $arPriceType['ID']]] . "1"] = $arPriceType['NAME_LANG'];
                        }
                    }
                }
            }

            /* Создание шапки файла (название колонок) свойства торговых предложений */
            if ($arProfileSettings['TRADING_OFFERS']['VALUE'] == 'on' || $_POST['TRADING_OFFERS'] == 'on') {
                if ($_POST['PRIMER'] !== 'Y') {
                    foreach ($arProfileSettings['PROPERTIES_TRADING_OFFERS']['VALUE'] as $onePropTO) {
                        $sheet = Main::SetHeaderColumn($alf, $SORT['PROPERTY_OFFERS_' . $onePropTO], 'PROPERTY_OFFERS_' . $onePropTO, $arPropertyTO, $sheet);
                        $arSelectProperty[] = 'PROPERTY_' . $onePropTO;  /* добавление в выборку только нужных свойств */
                    }
                } else {
                    foreach ($_POST['PROPERTIES_TRADING_OFFERS'] as $onePropTO) {
                        $PRIMER[$alf[$SORT['PROPERTY_OFFERS_' . $onePropTO]] . "1"] = $arPropertyTO['VALUES']['PROPERTY_OFFERS_' . $onePropTO];
                        $arSelectPropertyOFFERS[] = 'PROPERTY_' . $onePropTO;
                    }
                }
            }
            if ($_POST['PRIMER'] == "Y") {
                $result['PRIMER'] = $PRIMER;
                $result['SORT'] = $SORT;
                $result['arSelectProperty'] = $arSelectProperty;
                $result['arSelectPropertyOFFERS'] = $arSelectPropertyOFFERS;
                echo json_encode($result);
            } else {
                /* Сохранение нового файла */
                if ($arProfileSettings['METHOD']['VALUE'] == 1) { /* Если метод выгрузки AJAX*/
                    $result = json_encode(array('SORT' => $SORT, 'arSelectProperty' => $arSelectProperty, 'xls' => serialize($xls)));
                } elseif ($arProfileSettings['METHOD']['VALUE'] == 4) {
                    $result = json_encode(array('SORT' => $SORT, 'arSelectProperty' => $arSelectProperty));
                    Main::SaveFile($arProfileSettings, $xls);
                }
                echo $result;
            }

        } else { /* Шапка файла создана, создём тело */
            if ($_POST['PRIMER'] !== "Y") {
                $JSON = json_decode($_POST['DATA'], true);
                $nPageSize = 10;
            } else {
                $JSON = json_decode($_POST['AJAX_SHAG_2'], true);
                $nPageSize = 3;
                $SORT = $JSON['SORT'];
                $arProfileSettings['PICTURES']['VALUE'] = $_POST['PICTURES'];
                $arProfileSettings['IBLOCK_ID']['VALUE'] = $_POST['IBLOCK_ID'];
                $arProfileSettings['FIELDS']['VALUE'] = $_POST['FIELDS'];
                $arProfileSettings['PICTURES_SIZE']['VALUE'] = $_POST['PICTURES_SIZE'];
            }
            $arSelectProperty = $JSON['arSelectProperty'];
            $SHAG = 1; /* Значение номер страницы для выборки элементов (если не пустое то в следующей строке присваивается)*/
            if (!empty($JSON['SHAG'])) {
                $SHAG = $JSON['SHAG'];
            }
            $ROW = 2;/* Значение строки файла для записи элементов (если не пустое то в следующей строке присваивается)*/
            if (!empty($JSON['ROW'])) {
                $ROW = $JSON['ROW'];
            }
            $arFilter = [
                'IBLOCK_ID' => $arProfileSettings['IBLOCK_ID']['VALUE'],
            ];
            $arSelect = $arProfileSettings['FIELDS']['VALUE'];
            $arSelect[] = 'IBLOCK_ID';
            $arSelect[] = 'ID';
            $obItems = CIBlockElement::GetList(array(), $arFilter, false, array('iNumPage' => $SHAG, 'nPageSize' => $nPageSize), $arSelect);
            $result = Main::CreateResult($obItems, $JSON);
            if ($_POST['PRIMER'] !== "Y") {
                if ($arProfileSettings['METHOD']['VALUE'] == 1) {
                    $xls = unserialize($JSON['xls']);
                } elseif ($arProfileSettings['METHOD']['VALUE'] == 4) {
                    /* Загрузка существующего файла */
                    $xls = PHPExcel_IOFactory::load(str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'] . '/' . $arProfileSettings['PATH_EXPORT_FILE']['VALUE'] . $arProfileSettings['FILE_NAME']['VALUE'] . '.xlsx'));
                }
                $sheet = $xls->getActiveSheet();
            }
            $PRODUCTS = [];
            /* добавление в файл полей элемента и его свойства */
            while ($ob = $obItems->GetNextElement()) {
                $el = Main::GetCustFields($ob); /* Получение полей элемента без полей со знаком ~ */
                $tempProp = $ob->GetProperties(false, array('ID' => $arSelectProperty));
                foreach ($tempProp as $tempPropKey => $tempPropValue) {
                    $el['PROPERTY_' . $tempPropValue['ID']] = $tempPropValue['VALUE'];
                }
                $ob = $el;
                foreach ($ob as $key => $oneProp) { /* Перебор полей и свойств элемента */
                    if (stripos($key, '_ID') == false && $key !== 'ID') {
                        $ModProp = Main::ModifierProperty($arProfileSettings, $key, $oneProp); /* Определение свойство и тип свойства или стандартное поле */
                        $key = $ModProp['key'];
                        if ($_POST['PRIMER'] !== "Y") {
                            if ($ModProp['PROP']['PROPERTY_TYPE'] == 'F' || $key == 'FIELD_PREVIEW_PICTURE' || $key == 'FIELD_DETAIL_PICTURE') { /* проверка типа свойства или поля на файл */
                                if ($ModProp['PROP']['MULTIPLE'] == 'Y') { /* Если значение свойства множественое */
                                    $count = 1;
                                    if ($arProfileSettings['PICTURES']['VALUE'] == 1) {
                                        foreach ($oneProp as $valueProp) {
                                            $sheet = Main::CreateImage(CFile::GetPath($valueProp), $alf[$SORT[$key]], $ROW, $sheet, $arProfileSettings['PICTURES_SIZE']['VALUE'], $count);
                                            $count++;
                                        }
                                    } else {
                                        $text = '';
                                        foreach ($oneProp as $valueProp) {
                                            $text .= CFile::GetPath($valueProp) . '; ';
                                        }
                                        $sheet->setCellValue($alf[$SORT[$key]] . $ROW, $text);
                                        $sheet->getColumnDimension($alf[$SORT[$key]])->setAutoSize(true);
                                    }
                                } else {
                                    if ($arProfileSettings['PICTURES']['VALUE'] == 1) {
                                        $sheet = Main::CreateImage(CFile::GetPath($oneProp), $alf[$SORT[$key]], $ROW, $sheet, $arProfileSettings['PICTURES_SIZE']['VALUE'], 1);
                                    }else{
                                        $sheet->setCellValue($alf[$SORT[$key]] . $ROW, CFile::GetPath($oneProp));
                                        $sheet->getColumnDimension($alf[$SORT[$key]])->setAutoSize(true);
                                    }
                                }
                            } else {
                                if ($ModProp['PROP']['MULTIPLE'] == 'Y') {
                                    $text = '';
                                    foreach ($oneProp as $valueProp) {
                                        $text .= $valueProp . ' ';
                                    }
                                    $sheet->setCellValue($alf[$SORT[$key]] . $ROW, $text);
                                } else {
                                    $sheet->setCellValue($alf[$SORT[$key]] . $ROW, $oneProp);
                                }

                            }
                        } else { /* Тут собирается тело файла для примера на странице настроек */
                            if ($ModProp['PROP']['PROPERTY_TYPE'] == 'F' || $key == 'FIELD_PREVIEW_PICTURE' || $key == 'FIELD_DETAIL_PICTURE') { /* проверка типа свойства или поля на файл */
                                if ($ModProp['PROP']['MULTIPLE'] == 'Y') {
                                    $text = '';
                                    foreach ($oneProp as $valueProp) {
                                        if ($arProfileSettings['PICTURES']['VALUE'] == 1) {
                                            $text .= '<img width="' . $arProfileSettings['PICTURES_SIZE']['VALUE'] . 'px" src="' . CFile::GetPath($valueProp) . '">';
                                        }else{
                                            $text .= CFile::GetPath($valueProp) . '; ';
                                        }
                                    }
                                    $JSON['PRIMER'][$alf[$SORT[$key]] . $ROW] = $text;
                                } else {
                                    if ($arProfileSettings['PICTURES']['VALUE'] == 1) {
                                        $JSON['PRIMER'][$alf[$SORT[$key]] . $ROW] = '<img width="' . $arProfileSettings['PICTURES_SIZE']['VALUE'] . 'px" src="' . CFile::GetPath($oneProp) . '">';
                                    }else{
                                        $JSON['PRIMER'][$alf[$SORT[$key]] . $ROW] = CFile::GetPath($oneProp);
                                    }
                                }
                            } else {
                                if ($ModProp['PROP']['MULTIPLE'] == 'Y') {
                                    $text = '';
                                    foreach ($oneProp as $valueProp) {
                                        $text .= $valueProp . ' ';
                                    }
                                    $JSON['PRIMER'][$alf[$SORT[$key]] . $ROW] = $text;
                                } else {
                                    $JSON['PRIMER'][$alf[$SORT[$key]] . $ROW] = $oneProp;
                                }
                            }
                        }
                    }
                }
                if (\Bitrix\Main\Loader::includeModule('catalog')) {
                    /* Создание фильтра для цен */
                    $PRODUCTS['VALUES'][$ob['ID']] = $ROW;
                    $PRODUCTS['FILTER']['PRODUCT_ID'][] = $ob['ID'];
                    /* Конец создания фильтра для цен  продолжение в Торговых предложениях */

                    /* Добавление торговых предложений */
                    if ($arProfileSettings['TRADING_OFFERS']['VALUE'] == 'on' || $_POST['TRADING_OFFERS'] == 'on') {
                        $propTradingOffers = [];
                        if ($_POST['PRIMER'] !== "Y") {
                            foreach ($arProfileSettings['PROPERTIES_TRADING_OFFERS']['VALUE'] as $onePropTO) {
                                $propTradingOffers[] = 'PROPERTY_' . $onePropTO;
                            }
                        } else {
                            foreach ($_POST['PROPERTIES_TRADING_OFFERS'] as $onePropTO) {
                                $propTradingOffers[] = 'PROPERTY_' . $onePropTO;
                            }
                        }
                        $propTradingOffers[] = 'NAME';
                        $arTradingOffers = [];
                        $arTradingOffers = CCatalogSKU::getOffersList(
                            $productID = array($ob['ID']), // массив ID товаров
                            $iblockID = $arProfileSettings['IBLOCK_ID']['VALUE'], // указываете ID инфоблока только в том случае, когда ВЕСЬ массив товаров из одного инфоблока и он известен
                            $skuFilter = array(), // дополнительный фильтр предложений. по умолчанию пуст.
                            $fields = $propTradingOffers,  // массив полей предложений. даже если пуст - вернет ID и IBLOCK_ID
                            $propertyFilter = array()
                        );
                        $arTradingOffers = current($arTradingOffers);
                        foreach ($arTradingOffers as $oneTradingOffers) {
                            $ROW++;
                            foreach ($oneTradingOffers as $key => $onePropTP) {
                                if ($key !== 'ID' && $key !== 'IBLOCK_ID' && $key !== 'PARENT_ID' && stripos($key, '_ID') == false) {
                                    if ($key == 'NAME') {
                                        if ($_POST['PRIMER'] !== "Y") {
                                            $sheet->setCellValue($alf[$SORT['FIELD_' . $key]] . $ROW, $onePropTP);
                                        } else {
                                            $JSON['PRIMER'][$alf[$SORT['FIELD_' . $key]] . $ROW] = $onePropTP;
                                        }
                                    } else {
                                        $key = str_replace('_VALUE', '', str_replace('PROPERTY_', 'PROPERTY_OFFERS_', $key));
                                        if ($_POST['PRIMER'] !== "Y") {
                                            $sheet->setCellValue($alf[$SORT[$key]] . $ROW, $onePropTP);
                                        } else {
                                            $JSON['PRIMER'][$alf[$SORT[$key]] . $ROW] = $onePropTP;
                                        }
                                    }
                                }
                            }
                            $PRODUCTS['VALUES'][$oneTradingOffers['ID']] = $ROW;
                            $PRODUCTS['FILTER']['PRODUCT_ID'][] = $oneTradingOffers['ID'];
                        }
                    }
                }
                /* Конец добавления торговых предложений*/
                $ROW++;
            }
            /* Конец добавление в файл полей элемента и его свойства */

            if (\Bitrix\Main\Loader::includeModule('catalog')) {
                /* Добавление цен */
                $arFilterPrice = $PRODUCTS['FILTER'];
                if ($_POST['PRIMER'] !== "Y") {
                    $arFilterPrice['CATALOG_GROUP_ID'] = $arProfileSettings['PRICE']['VALUE'];
                } else {
                    $arFilterPrice['CATALOG_GROUP_ID'] = $_POST['PRICE'];
                }
                $obPrice = \Bitrix\Catalog\PriceTable::getList(array('filter' => $arFilterPrice, 'select' => array('PRODUCT_ID', 'CATALOG_GROUP_ID', 'PRICE')));
                while ($ob = $obPrice->fetch()) {
                    if (!empty($SORT['PRICE_' . $ob['CATALOG_GROUP_ID']])) {
                        if ($_POST['PRIMER'] !== "Y") {
                            $sheet->setCellValue($alf[$SORT['PRICE_' . $ob['CATALOG_GROUP_ID']]] . $PRODUCTS['VALUES'][$ob['PRODUCT_ID']], $ob['PRICE']);
                        } else {
                            $JSON['PRIMER'][$alf[$SORT['PRICE_' . $ob['CATALOG_GROUP_ID']]] . $PRODUCTS['VALUES'][$ob['PRODUCT_ID']]] = $ob['PRICE'];
                        }
                    }
                }
                /* Конец добавления цен */
            }
            if ($_POST['PRIMER'] !== "Y") {
                if ($arProfileSettings['METHOD']['VALUE'] == 4) {
                    Main::SaveFile($arProfileSettings, $xls);
                }
                if ($result['SHAG'] > $obItems->NavPageCount) {
                    $result['FINISH'] = "Y";
                    $result['PERCENT'] = 100;
                    if ($arProfileSettings['METHOD']['VALUE'] == 1) {
                        Main::SaveFile($arProfileSettings, $xls);
                    }
                }
                $result['ROW'] = $ROW;
                if ($arProfileSettings['METHOD']['VALUE'] == 1) {
                    $result['xls'] = serialize($xls);
                }
                echo json_encode($result);
            } else {
                if (!empty($SORT)) {
                    echo '<table class="table_primer"><tr><td></td>';
                    foreach ($SORT as $oneSort) {
                        echo '<td>' . $alf[$oneSort] . '</td>';
                    }
                    echo '</tr>';
                    for ($k = 1; $k < $ROW; $k++) {
                        echo '<tr><td>' . $k . '</td>';
                        foreach ($SORT as $oneSort) {
                            echo '<td>' . $JSON['PRIMER'][$alf[$oneSort] . $k] . '</td>';
                        }
                        echo '</tr>';
                    }

                    echo '</table>';
                }
            }
        }
    }
}
<?
AddEventHandler("iblock", "OnAfterIBlockElementAdd", array("Iblock", "addLog"));
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", array("Iblock", "addLog"));

class Iblock
{
    protected static $handlerDisallow = false;
    protected static $IBLOCK_CODE = 'LOG';

    // создаем обработчик события "OnAfterIBlockElementAdd" и OnAfterIBlockElementUpdate
    public static function addLog(&$arFields)
    {
        /* проверяем, что обработчик уже запущен */
        if (self::$handlerDisallow)
            return;
        /* взводим флаг запуска */
        self::$handlerDisallow = true;

        /* Проверка успешности добавления или изменения элемента, вызвавшего обработчик события */
        if (!$arFields["RESULT"])
            AddMessage2Log("Ошибка добавления или изменения записи: " . $arFields["RESULT_MESSAGE"]);
        else {
            AddMessage2Log("Запись с ID-" . $arFields["ID"] . " добавлена или изменена.");
            $IBLOCK_ID = self::FindIBlockID(self::$IBLOCK_CODE);

            /* Проверка, что событие вызвано элементом не инфоблока LOG*/
            if ($arFields["IBLOCK_ID"] != $IBLOCK_ID) {

                /* Получаю имя и код инфоблока, в котором изменен элемент, для название раздела LOG */
                $resIBlock = CIBlock::GetByID($arFields["IBLOCK_ID"]);
                if ($ar_res = $resIBlock->Fetch()) {
                    $iBlockName = $ar_res["NAME"];
                    $sectionName = $iBlockName . "_" . $ar_res["IBLOCK_CODE"];
                }

                /* Проверяю - есть ли такой раздел в логе, добавляю - если нет */
                $resSection = CIBlockSection::GetList(array(),
                    array('IBLOCK_ID' => $IBLOCK_ID, 'NAME' => $sectionName));
                if ($ar_res = $resSection->Fetch()) {
                    $sectionID = $ar_res["ID"];
                } else {
                    $arFieldsSection = array(
                        "ACTIVE" => 'Y',
                        "IBLOCK_ID" => $IBLOCK_ID,
                        "NAME" => $sectionName,
                    );
                    $section = new CIBlockSection;
                    if ($sectionID = $section->Add($arFieldsSection)) {
                        AddMessage2Log("Добавлен раздел с ID-" . $sectionID);
                    } else {
                        AddMessage2Log("Ошибка добавления раздела:" . $section->LAST_ERROR);
                    }
                }

                /* Получаю текст для анонса элемента LOG */
                $resElement = CIBlockElement::GetByID($arFields["ID"]);
                while ($ar_res = $resElement->GetNext()) {
                    $IBLOCK_SECTION_ID = $ar_res["IBLOCK_SECTION_ID"];
                }
                $parents = "";
                if ($IBLOCK_SECTION_ID)
                    $parents = self::FindParents($IBLOCK_SECTION_ID, $parents);
                $previewTextElement = $iBlockName . "->" . $parents . $arFields["NAME"];

                /* Массив, содержащий значения полей элемента LOG*/
                $arFieldsElement = [
                    "IBLOCK_ID" => $IBLOCK_ID,
                    "IBLOCK_SECTION_ID" => $sectionID,
                    "NAME" => $arFields["ID"],
                    "ACTIVE" => 'Y',
                    "ACTIVE_FROM" => date('d.m.Y'),
                    "PREVIEW_TEXT" => $previewTextElement,
                ];

                /* Проверяю - есть ли такой элемент в LOG */
                $element = new CIBlockElement;
                $resElement = CIBlockElement::GetList(array(),
                    array('IBLOCK_ID' => $IBLOCK_ID, 'NAME' => $arFields["ID"]));
                /* Изменяю элемент в LOG */
                if ($ar_res = $resElement->Fetch()) {
                    $elementID = $ar_res["ID"];
                    if ($element->Update($elementID, $arFieldsElement)) {
                        AddMessage2Log("Изменен элемент с ID-" . $elementID);
                    } else {
                        AddMessage2Log("Ошибка изменения элемента:" . $element->LAST_ERROR);
                    }
                } /* Добавляю элемент в LOG */
                else {
                    if ($elementID = $element->Add($arFieldsElement)) {
                        AddMessage2Log("Добавлен элемент с ID-" . $elementID);
                    } else {
                        AddMessage2Log("Ошибка добавления элемента:" . $element->LAST_ERROR);
                    }
                }
            }
        }
        /* вновь разрешаем запускать обработчик */
        self::$handlerDisallow = false;
    }

    function OnBeforeIBlockElementAddHandler(&$arFields)
    {
        $iQuality = 95;
        $iWidth = 1000;
        $iHeight = 1000;
        /*
         * Получаем пользовательские свойства
         */
        $dbIblockProps = \Bitrix\Iblock\PropertyTable::getList(array(
            'select' => array('*'),
            'filter' => array('IBLOCK_ID' => $arFields['IBLOCK_ID'])
        ));
        /*
         * Выбираем только свойства типа ФАЙЛ (F)
         */
        $arUserFields = [];
        while ($arIblockProps = $dbIblockProps->Fetch()) {
            if ($arIblockProps['PROPERTY_TYPE'] == 'F') {
                $arUserFields[] = $arIblockProps['ID'];
            }
        }
        /*
         * Перебираем и масштабируем изображения
         */
        foreach ($arUserFields as $iFieldId) {
            foreach ($arFields['PROPERTY_VALUES'][$iFieldId] as &$file) {
                if (!empty($file['VALUE']['tmp_name'])) {
                    $sTempName = $file['VALUE']['tmp_name'] . '_temp';
                    $res = \CAllFile::ResizeImageFile(
                        $file['VALUE']['tmp_name'],
                        $sTempName,
                        array("width" => $iWidth, "height" => $iHeight),
                        BX_RESIZE_IMAGE_PROPORTIONAL_ALT,
                        false,
                        $iQuality);
                    if ($res) {
                        rename($sTempName, $file['VALUE']['tmp_name']);
                    }
                }
            }
        }

        if ($arFields['CODE'] == 'brochures') {
            $RU_IBLOCK_ID = \Only\Site\Helpers\IBlock::getIblockID('DOCUMENTS', 'CONTENT_RU');
            $EN_IBLOCK_ID = \Only\Site\Helpers\IBlock::getIblockID('DOCUMENTS', 'CONTENT_EN');
            if ($arFields['IBLOCK_ID'] == $RU_IBLOCK_ID || $arFields['IBLOCK_ID'] == $EN_IBLOCK_ID) {
                \CModule::IncludeModule('iblock');
                $arFiles = [];
                foreach ($arFields['PROPERTY_VALUES'] as $id => &$arValues) {
                    $arProp = \CIBlockProperty::GetByID($id, $arFields['IBLOCK_ID'])->Fetch();
                    if ($arProp['PROPERTY_TYPE'] == 'F' && $arProp['CODE'] == 'FILE') {
                        $key_index = 0;
                        while (isset($arValues['n' . $key_index])) {
                            $arFiles[] = $arValues['n' . $key_index++];
                        }
                    } elseif ($arProp['PROPERTY_TYPE'] == 'L' && $arProp['CODE'] == 'OTHER_LANG' && $arValues[0]['VALUE']) {
                        $arValues[0]['VALUE'] = null;
                        if (!empty($arFiles)) {
                            $OTHER_IBLOCK_ID = $RU_IBLOCK_ID == $arFields['IBLOCK_ID'] ? $EN_IBLOCK_ID : $RU_IBLOCK_ID;
                            $arOtherElement = \CIBlockElement::GetList([],
                                [
                                    'IBLOCK_ID' => $OTHER_IBLOCK_ID,
                                    'CODE' => $arFields['CODE']
                                ], false, false, ['ID'])
                                ->Fetch();
                            if ($arOtherElement) {
                                /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                                \CIBlockElement::SetPropertyValues($arOtherElement['ID'], $OTHER_IBLOCK_ID, $arFiles, 'FILE');
                            }
                        }
                    } elseif ($arProp['PROPERTY_TYPE'] == 'E') {
                        $elementIds = [];
                        foreach ($arValues as &$arValue) {
                            if ($arValue['VALUE']) {
                                $elementIds[] = $arValue['VALUE'];
                                $arValue['VALUE'] = null;
                            }
                        }
                        if (!empty($arFiles && !empty($elementIds))) {
                            $rsElement = \CIBlockElement::GetList([],
                                [
                                    'IBLOCK_ID' => \Only\Site\Helpers\IBlock::getIblockID('PRODUCTS', 'CATALOG_' . $RU_IBLOCK_ID == $arFields['IBLOCK_ID'] ? '_RU' : '_EN'),
                                    'ID' => $elementIds
                                ], false, false, ['ID', 'IBLOCK_ID', 'NAME']);
                            while ($arElement = $rsElement->Fetch()) {
                                /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                                \CIBlockElement::SetPropertyValues($arElement['ID'], $arElement['IBLOCK_ID'], $arFiles, 'FILE');
                            }
                        }
                    }
                }
            }
        }
    }

    /* Поиск ID инфоблока LOG*/
    static function FindIBlockID(string $IBLOCK_CODE)
    {
        $res = CIBlock::GetList(array(), ['CODE' => $IBLOCK_CODE]);
        while ($ar_res = $res->Fetch()) {
            $IBLOCK_ID = $ar_res['ID'];
        }
        return $IBLOCK_ID;
    }

    /*Поиск всех родительских групп */
    static function FindParents($sectionID, string &$parents)
    {
        $resSection = CIBlockSection::GetByID($sectionID);
        while ($ar_res = $resSection->GetNext()) {
            $parents = $ar_res["NAME"] . "->" . $parents;
            if ($ar_res["IBLOCK_SECTION_ID"])
                $parents = self::FindParents($ar_res["IBLOCK_SECTION_ID"], $parents);
        }
        return $parents;
    }

}


/* Он должен удалять все логи, кроме 10 самых новых. */
function clearOldLogs()
{
    \Bitrix\Main\Loader::includeModule('iblock');
    /* Поиск ID инфоблока LOG*/
    $IBLOCK_CODE = 'LOG';
    $res = CIBlock::GetList(array(), ['CODE' => $IBLOCK_CODE]);
    while ($ar_res = $res->Fetch()) {
        $IBLOCK_ID = $ar_res['ID'];
    }
    /* Получаю все элементы в LOG, кроме 10 самых новых */
    $resElement = CIBlockElement::GetList(
        array('ACTIVE_FROM' => 'DESC'),
        array('IBLOCK_ID' => $IBLOCK_ID),
        array("ID", "ACTIVE_FROM")
    );
    if ($resElement && count($resElement) > 10) {
        $arDelElement = array_slice($resElement, 10);
        /* Удаляю элементы в LOG */
        if ($ar_del = $arDelElement->Fetch()) {
            if (CIBlockElement::Delete($ar_del['ID'])) {
                AddMessage2Log("Запись с ID-" . $ar_del['ID'] . " удалена.");
            } else {
                AddMessage2Log("Ошибка удаления записи с ID-" . $ar_del['ID']);
            }
        }
    }
}

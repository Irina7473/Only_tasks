<?php

namespace Only\Site\Agents;


class Iblock
{
    protected static  $IBLOCK_CODE = 'LOG';
    public static function clearOldLogs()
    {
        /* Он должен удалять все логи, кроме 10 самых новых. */
        $IBLOCK_ID = self::FindIBlockID(self::$IBLOCK_CODE);
        /* Получаю все элементы в LOG, кроме 10 самых новых */
        $resElement = CIBlockElement::GetList(
            Array('ACTIVE_FROM'=>'DESC'),
            Array('IBLOCK_ID'=>$IBLOCK_ID),
            Array("ID", "ACTIVE_FROM")
        );
        if($resElement && count($resElement)>10) {
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

    public static function example()
    {
        global $DB;
        if (\Bitrix\Main\Loader::includeModule('iblock')) {
            $iblockId = \Only\Site\Helpers\IBlock::getIblockID('QUARRIES_SEARCH', 'SYSTEM');
            $format = $DB->DateFormatToPHP(\CLang::GetDateFormat('SHORT'));
            $rsLogs = \CIBlockElement::GetList(['TIMESTAMP_X' => 'ASC'], [
                'IBLOCK_ID' => $iblockId,
                '<TIMESTAMP_X' => date($format, strtotime('-1 months')),
            ], false, false, ['ID', 'IBLOCK_ID']);
            while ($arLog = $rsLogs->Fetch()) {
                \CIBlockElement::Delete($arLog['ID']);
            }
        }
        return '\\' . __CLASS__ . '::' . __FUNCTION__ . '();';
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
}

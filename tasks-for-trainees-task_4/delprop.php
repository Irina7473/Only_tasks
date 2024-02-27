<?php
//УДАЛЕНИЕ ДУБЛИКАТОВ ЗНАЧЕНИЙ СВОЙСТВ ТИПА СПИСОК
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
if (!$USER->IsAdmin()) {
    LocalRedirect('/');
}
\Bitrix\Main\Loader::includeModule('iblock');

$arProps = [];

$IBLOCK_CODE = 'VACANCIES';
$res = CIBlock::GetList(array(), ['CODE' => $IBLOCK_CODE]);
while ($ar_res = $res->Fetch()) {
    $IBLOCK_ID = $ar_res['ID'];
}

$rsProp = CIBlockPropertyEnum::GetList(
    ["SORT" => "ASC", "VALUE" => "ASC"],
    ['IBLOCK_ID' => $IBLOCK_ID]
);
while ($arProp = $rsProp->GetNext()) {
    $arProps[$arProp['ID']] = $arProp['VALUE'];
}
foreach ($arProps as $val)
    echo $val . " ";

$arValProps = array_values($arProps);
$arValProps = array_unique($arValProps);
$arDelProps = [];
foreach ($arValProps as $val)
{
    $temp=false;
    foreach ($arProps as $key => $value)
    {
        if(is_numeric($value)) array_push($arDelProps, $key);
        if($val==$value){
            if(!$temp) $temp=true;
            else array_push($arDelProps, $key);
        }
    }
}

foreach ($arDelProps as $key)
{
    if ($temp =CIBlockPropertyEnum::Delete($key)) {
        echo "Удалено значение свойства с ID : " . $key . "<br>";
    } else {
        echo "Error: " . $temp->LAST_ERROR . '<br>';
    }
}
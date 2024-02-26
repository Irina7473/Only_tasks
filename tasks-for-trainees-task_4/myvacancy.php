<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
if (!$USER->IsAdmin()) {
    LocalRedirect('/');
}
\Bitrix\Main\Loader::includeModule('iblock');
$row = 1;
$IBLOCK_ID = 6;
$el = new CIBlockElement;
$arProps = [];

$rsProp = CIBlockPropertyEnum::GetList(
    ["SORT" => "ASC", "VALUE" => "ASC"],
    ['IBLOCK_ID' => $IBLOCK_ID]
);
while ($arProp = $rsProp->Fetch()) {
    $key = trim($arProp['VALUE']);
    $arProps[$arProp['PROPERTY_CODE']][$key] = $arProp['ID'];
}

if (($file = fopen("vacancy.csv", "r")) !== false) {
    while (($data = fgetcsv($file, 1000, ",")) !== false) {
        if ($row == 1) {
            $row++;
            continue;
        }
        $row++;

        $PROP['ACTIVITY'] = $data[9];   //Тип занятости
        $PROP['FIELD'] = $data[11];     //Сфера деятельности
        $PROP['OFFICE'] = $data[1];     //Комбинат
        $PROP['EMAIL'] = $data[12];
        $PROP['LOCATION'] = $data[2];   //Место
        $PROP['TYPE'] = $data[8];       //Тип вакансии
        $PROP['SALARY_TYPE'] = '';      //Зарплата
        $PROP['SALARY_VALUE'] = $data[7];
        $PROP['REQUIRE'] = $data[4];
        $PROP['DUTY'] = $data[5];
        $PROP['CONDITIONS'] = $data[6];
        $PROP['SCHEDULE'] = $data[10];     //График
        $PROP['DATE'] = date('d.m.Y');

        foreach ($PROP as $key => &$value) {
            $value = trim($value);
            $value = str_replace('\n', '', $value);
            if (stripos($value, '•') !== false) {
                $value = explode('•', $value);
                array_splice($value, 0, 1);
                foreach ($value as &$str) {
                    $str = trim($str);
                }
            } elseif ($arProps[$key]) {
                $arSimilar = [];
                foreach ($arProps[$key] as $propKey => $propVal) {
                    if ($key == 'OFFICE') {
                        $value = strtolower($value);
                        if ($value == 'центральный офис') {
                            $value .= 'свеза ' . $data[2];
                        } elseif ($value == 'лесозаготовка') {
                            $value = 'свеза ресурс ' . $value;
                        } elseif ($value == 'свеза тюмень') {
                            $value = 'свеза тюмени';
                        }
                        $arSimilar[similar_text($value, $propKey)] = $propVal;
                    }

                    if ($key != 'SALARY_TYPE') {
                        if (stripos($value, '/') !== false || stripos($value, ',') !== false) {
                            $arValue = preg_split('\/,', $value);
                        } else $arValue[0] = $value;
                        foreach ($arValue as $item) {
                            if (stripos($propKey, $item) !== false) {
                                $value = $propVal;
                                break;
                            }
                            if (similar_text($propKey, $value) > 50) {
                                $value = $propVal;
                            }
                        }
                    }
                }
                if ($key == 'OFFICE' && !is_numeric($value)) {
                    ksort($arSimilar);
                    $value = array_pop($arSimilar);
                }
            }
        }

        if ($PROP['SALARY_VALUE'] == '-') {
            $PROP['SALARY_VALUE'] = '';
        } elseif ($PROP['SALARY_VALUE'] == 'по договоренности') {
            $PROP['SALARY_VALUE'] = '';
            $PROP['SALARY_TYPE'] = $arProps['SALARY_TYPE']['договорная'];
        } else {
            $arSalary = explode(' ', $PROP['SALARY_VALUE']);
            if ($arSalary[0] == 'от' || $arSalary[0] == 'до') {
                $PROP['SALARY_TYPE'] = $arProps['SALARY_TYPE'][$arSalary[0]];
                array_splice($arSalary, 0, 1);
                $PROP['SALARY_VALUE'] = implode(' ', $arSalary);
            } else {
                $PROP['SALARY_TYPE'] = $arProps['SALARY_TYPE']['='];
            }
        }

        $arLoadProductArray = [
            "MODIFIED_BY" => $USER->GetID(),
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID" => $IBLOCK_ID,
            "PROPERTY_VALUES" => $PROP,
            "NAME" => $data[3],
            "ACTIVE" => end($data) ? 'Y' : 'N',
        ];
        if ($PRODUCT_ID = $el->Add($arLoadProductArray)) {
            echo "Добавлен элемент с ID : " . $PRODUCT_ID . "<br>";
        } else {
            echo "Error: " . $el->LAST_ERROR . '<br>';
        }
    }
    fclose($file);
}
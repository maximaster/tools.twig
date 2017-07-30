<?php
$arResult = array('a', 'b', 'c', 'd', 'bc', 'cd', 'abc', 'abcd');
$arResult = array_combine($arResult, $arResult);

if (!empty($arParams['additionalContext'])) {
    $arResult += $arParams['additionalContext'];
}

$this->IncludeComponentTemplate();
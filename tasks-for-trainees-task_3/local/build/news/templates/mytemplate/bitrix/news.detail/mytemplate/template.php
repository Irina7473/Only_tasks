<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
//$this->addExternalCss("/local/templates/build/css/common.css");
?>
    <div class="article-card">
        <? if ($arParams["DISPLAY_NAME"] != "N" && $arResult["NAME"]): ?>
            <div class="article-card__title"><?= $arResult["NAME"] ?></div>
        <? endif; ?>
        <? if ($arParams["DISPLAY_DATE"] != "N" && $arResult["DISPLAY_ACTIVE_FROM"]): ?>
            <div class="article-card__date"><?= $arResult["DISPLAY_ACTIVE_FROM"] ?></div>
        <? endif; ?>
        <div class="article-card__content">
            <? if ($arParams["DISPLAY_PICTURE"] != "N" && is_array($arResult["DETAIL_PICTURE"])): ?>
                <div class="article-card__image sticky">
                    <img
                        class="article-card__image"
                        src="<?= $arResult["DETAIL_PICTURE"]["SRC"] ?>"
                        width="<?= $arResult["DETAIL_PICTURE"]["WIDTH"] ?>"
                        height="<?= $arResult["DETAIL_PICTURE"]["HEIGHT"] ?>"
                        alt="<?= $arResult["DETAIL_PICTURE"]["ALT"] ?>"
                        title="<?= $arResult["DETAIL_PICTURE"]["TITLE"] ?>"
                    />
                </div>
            <? endif ?>
            <div class="article-card__text">
                <? if ($arResult["NAV_RESULT"]): ?>
                    <? if ($arParams["DISPLAY_TOP_PAGER"]): ?><?= $arResult["NAV_STRING"] ?><br/><? endif; ?>
                    <div class="block-content" data-anim="anim-3"><? echo $arResult["NAV_TEXT"]; ?></div>
                    <? if ($arParams["DISPLAY_BOTTOM_PAGER"]): ?><br/><?= $arResult["NAV_STRING"] ?><? endif; ?>
                <? elseif ($arResult["DETAIL_TEXT"] <> ''): ?>
                    <div class="block-content" data-anim="anim-3"><? echo $arResult["DETAIL_TEXT"]; ?></div>
                <? else: ?>
                    <div class="block-content" data-anim="anim-3"><? echo $arResult["PREVIEW_TEXT"]; ?></div>
                <? endif ?>
            </div>
			<a class="article-card__button" href="/snova-novosti"><?=GetMessage("T_NEWS_DETAIL_BACK")?></a>
		</div>
	</div>

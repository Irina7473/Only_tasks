<?php  if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die(); ?>
<div id="barba-wrapper">
    <div class="article-list">
        <?php foreach($arResult["ITEMS"] as $arItem):?>
        <?php
        $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'],
            CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
        $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'],
            CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"),
            array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
        ?>
        <a class="article-item article-list__item" href="#" data-anim="anim-3">
            <div class="article-card__content">
                <?php if ($arParams["DISPLAY_PICTURE"] != "N" && is_array($arItem["PREVIEW_PICTURE"])): ?>
                    <?php if (!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])): ?>
                        <a href="<?= $arItem["DETAIL_PAGE_URL"] ?>">
                            <div class="article-item__background">
                                <img
                                        class="preview_picture"
                                        src="<?= $arItem["PREVIEW_PICTURE"]["SRC"] ?>"
                                        width="<?= $arItem["PREVIEW_PICTURE"]["WIDTH"] ?>"
                                        height="<?= $arItem["PREVIEW_PICTURE"]["HEIGHT"] ?>"
                                        alt="<?= $arItem["PREVIEW_PICTURE"]["ALT"] ?>"
                                        title="<?= $arItem["PREVIEW_PICTURE"]["TITLE"] ?>"
                                />
                            </div>
                        </a>
                    <?php else: ?>
                        <div class="article-item__background">
                            <img
                                    class="preview_picture"
                                    src="<?= $arItem["PREVIEW_PICTURE"]["SRC"] ?>"
                                    width="<?= $arItem["PREVIEW_PICTURE"]["WIDTH"] ?>"
                                    height="<?= $arItem["PREVIEW_PICTURE"]["HEIGHT"] ?>"
                                    alt="<?= $arItem["PREVIEW_PICTURE"]["ALT"] ?>"
                                    title="<?= $arItem["PREVIEW_PICTURE"]["TITLE"] ?>"
                            />
                        </div>
                    <?php endif; ?>
                <?php endif ?>

                <div class="article-card__text">
                    <div class="article-item__wrapper" id="<?= $this->GetEditAreaId($arItem['ID']); ?>">
                        <div class="article-item__title">
                            <?php if ($arParams["DISPLAY_NAME"] != "N" && $arItem["NAME"]): ?>
                            <?php if (!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])): ?>
                                <a href="<?php echo $arItem["DETAIL_PAGE_URL"] ?>"><b><?php echo $arItem["NAME"] ?></b></a>
                                <br/>
                            <?php else: ?>
                            <div class="article-item__title"<?php echo $arItem["NAME"] ?></div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <?php if ($arParams["DISPLAY_PREVIEW_TEXT"] != "N" && $arItem["PREVIEW_TEXT"]): ?>
                        <div class="article-item__content"><?php echo $arItem["PREVIEW_TEXT"]; ?></div>
                    <?php endif; ?>
                </div>
                <? if ($arParams["DISPLAY_DATE"] != "N" && $arResult["DISPLAY_ACTIVE_FROM"]): ?>
                    <div class="news-date-time"><?= $arResult["DISPLAY_ACTIVE_FROM"] ?></div>
                <? endif; ?>
            </div>
    </div>
    </a>
    <?php endforeach; ?>

    <?php if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
        <br /><?=$arResult["NAV_STRING"]?>
    <?php endif;?>
</div>
</div>
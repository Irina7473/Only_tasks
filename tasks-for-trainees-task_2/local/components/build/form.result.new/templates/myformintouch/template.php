<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
 $APPLICATION->SetTitle(""); 

if ($arResult["isFormErrors"] == "Y"):?><?= $arResult["FORM_ERRORS_TEXT"]; ?><?php endif; ?>
<? // = $arResult["FORM_NOTE"] ?>
<?php if ($arResult["isFormNote"] === "Y") : ?>
    Спасибо, ваша заявка принята!
<?php else :  ?>

<?= $arResult["FORM_HEADER"] ?>

<div class="contact-form">
    <div class="contact-form__head">
         <?php   if ($arResult["isFormTitle"]) {  ?>
         <div class="contact-form__head-title"><?= $arResult["FORM_TITLE"] ?></div>
         <?php
            }
			if ($arResult["isFormDescription"]) {
         ?>
          <div class="contact-form__head-text"><?= $arResult["FORM_DESCRIPTION"] ?></div>
   <?php  }   ?>
    </div>
    <br/>

	<form class="contact-form__form" id="In_touch"  action="/" method="POST">
		<div class="contact-form__form-inputs">
	<?  foreach ($arResult["QUESTIONS"] as $FIELD_SID => $arQuestion)
		{
 			if ($arQuestion['STRUCTURE'][0]['FIELD_TYPE'] == 'hidden')
			{ 
				echo $arQuestion["HTML_CODE"];
			}
			else
			{
    ?>
        <div class="<?= ($arQuestion['FIELD_SID'] == 'message' ? 'contact-form__form-message' : 'input contact-form__input');?>">
            <label class="input__label">
                <?if (is_array($arResult["FORM_ERRORS"]) && array_key_exists($FIELD_SID, $arResult['FORM_ERRORS'])):?>
                <span class="error-fld" title="<?=htmlspecialcharsbx($arResult["FORM_ERRORS"][$FIELD_SID])?>"></span>
                <?endif;?>
				<div class="input__label-text">
                	<?=$arQuestion["CAPTION"]?>
					<?if ($arQuestion["REQUIRED"] == "Y"):?><?=$arResult["REQUIRED_SIGN"];?><?endif;?> 
				</div>               
				<input class="input__input" <?=$arQuestion["HTML_CODE"]?>
			</label>
		</div>
    <?
			}	
		}
    ?>
		<div class="contact-form__bottom">
			<div class="contact-form__bottom-policy">Нажимая Отправить, Вы подтверждаете, что
				ознакомлены, полностью согласны и принимаете условия Согласия на обработку 
				персональных данных.
			</div>
			<input <?= (intval($arResult["F_RIGHT"]) < 10 ? "disabled=\"disabled\"" : ""); ?>
					class="form-button contact-form__bottom-button" type="submit" name="web_form_submit"
					value="<?= htmlspecialcharsbx(trim($arResult["arForm"]["BUTTON"]) == '' ? GetMessage("FORM_ADD") : $arResult["arForm"]["BUTTON"]); ?>"/>
		</div>

		<?= $arResult["FORM_FOOTER"] ?>
    </form>
	<div class="input__label-text">
		<?= $arResult["REQUIRED_SIGN"]; ?> - <?= GetMessage("FORM_REQUIRED_FIELDS") ?>
	</div>
</div>
<?php  endif;  ?>
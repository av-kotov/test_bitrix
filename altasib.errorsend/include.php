<?
/**
 *        Company developer: ALTASIB
 *        Site: http://www.altasib.ru
 *        E-mail: dev@altasib.ru
 *        Copyright (c) 2006-2018 ALTASIB
 */

global $DBType;
IncludeModuleLangFile(__FILE__);

Class ErrorSendMD
{
        function ErrorSendOnBeforeEndBufferContent()
        {
                global $APPLICATION;

                if(!IsModuleInstalled("altasib.errorsend"))
                        return false;

                if(!defined("ADMIN_SECTION") || ADMIN_SECTION!==true)
                {
                        CJSCore::Init(array("popup"));
                        $APPLICATION->AddHeadString("<script type=\"text/javascript\">
var ALXerrorSendMessages={
        'head':'".GetMessage("ALTASIB_ERROR_SEND_JS_HEAD")."',
        'footer':'".GetMessage("ALTASIB_ERROR_SEND_JS_FOOTER")."',
        'comment':'".GetMessage("ALTASIB_ERROR_SEND_JS_COMMENT")."',
        'TitleForm':'".GetMessage("ALTASIB_ERROR_SEND_JS_TITLEFORM")."',
        'ButtonSend':'".GetMessage("ALTASIB_ERROR_SEND_JS_BUTTONSEND")."',
        'LongText':'".GetMessage("ALTASIB_ERROR_SEND_JS_LONGTEXT")."',
        'LongText2':'".GetMessage("ALTASIB_ERROR_SEND_JS_LONGTEXT2")."',
        'cancel':'".GetMessage("ALTASIB_ERROR_CANCEL")."',
        'senderror':'".GetMessage("ALTASIB_ERROR_SENDERROR")."',
        'close':'".GetMessage("ALTASIB_ERROR_CLOSE")."',
        'text_ok':'".GetMessage("ALTASIB_ERROR_SEND_JS_TEXT_OK")."',
        'text_ok2':'".GetMessage("ALTASIB_ERROR_SEND_JS_TEXT_OK2")."'
}
</script>", false);

                        $APPLICATION->AddHeadString("<script type='text/javascript' async src='/bitrix/js/altasib.errorsend/error.js'></script>",false);
                        $APPLICATION->SetAdditionalCSS("/bitrix/js/altasib.errorsend/css/window.css");
                }
        }

        function ErrorSendOnProlog()
        {
                global $APPLICATION;
                if($_SERVER["REQUEST_METHOD"]=="POST"
                        && (isset($_REQUEST["AJAX_CALL"]) && $_REQUEST["AJAX_CALL"]=="Y")
                        && (isset($_REQUEST["ERROR_SEND"]) && $_REQUEST["ERROR_SEND"]=="Y")
                ){
                        if(!CModule::IncludeModule("altasib.errorsend"))
                                return;

                        $APPLICATION->RestartBuffer();

                        $arFields = $_POST;

                        $BX_UTF = false;
                        if(defined('BX_UTF'))
                                if(is_bool(BX_UTF))
                                        if(BX_UTF)
                                                $BX_UTF = true;

                        foreach ($arFields as $F_NAME=>$F_VALUE)
                        {
                                if($BX_UTF)
                                        $arFields[$F_NAME] = $F_VALUE;
                                else
                                        $arFields[$F_NAME] = mb_convert_encoding($F_VALUE, 'windows-1251', 'auto');
                        }
                        AddError($arFields);
                        die();
                }
        }
}

function AddError($arFields)
{
        global $DB,$APPLICATION;

        if(!CModule::IncludeModule("iblock"))
                return "";

        $iblockBaseCode = COption::GetOptionString("altasib_errorsend", "ERROR_SEND_IBLOCK_BASE_CODE", "Spell_errors_site");

        $arIB = CIBlock::GetList(false, array("CODE" => $iblockBaseCode."_".SITE_ID))->Fetch();
        if($arIB)
                $IBLOCK_ID = $arIB["ID"];
        else
        {
                $arIB = CIBlock::GetList(false, array("CODE" => 'spelling_errors_site'))->Fetch();
                if($arIB)
                $IBLOCK_ID = $arIB["ID"];
        }
        if($IBLOCK_ID==0)
        {
                echo 'error add';
                return;
        }
        $LIMIT_IP = COption::GetOptionInt("altasib_errorsend", "limit_ip", 0, SITE_ID);

        if(0 >= (int)$LIMIT_IP)
                $LIMIT_IP = COption::GetOptionInt("altasib_errorsend", "limit_ip", 30, "");

        $IP_ADDRESS = $_SERVER["REMOTE_ADDR"];
        if(intval($LIMIT_IP) > 0 && $IBLOCK_ID>0 && $IP_ADDRESS)
        {
                CTimeZone::Disable();
                $obElement = CIBlockElement::GetList(Array("id"=>"desc"), Array("IBLOCK_ID"=>$IBLOCK_ID, "PROPERTY_IP_ADDRESS" => $IP_ADDRESS), false, false, array("ID", "DATE_CREATE"));
                if($arElement = $obElement->Fetch())
                {
                        $site_format = CSite::GetDateFormat(); // DD.MM.YYYY HH:MI:SS
                        $stmp = MakeTimeStamp($arElement["DATE_CREATE"], $site_format);
                        if((time() - $stmp) < $LIMIT_IP)
                        {
                                echo GetMessage("ALTASIB_ERROR_SEND_ERROR_TEXT_LIMIT");
                                CTimeZone::Enable();
                                return;
                        }
                }
                CTimeZone::Enable();
        }

        $SectionCode = date("m.Y", time());
        $obSection = CIBlockSection::GetList(Array($by=>$order), Array("ACTIVE" => "Y", "IBLOCK_ID" => $IBLOCK_ID, "CODE" => $SectionCode), false);

        $arSection = $obSection->Fetch();
        if(!$arSection["ID"])
        {
                $bs = new CIBlockSection;
                $arSectionFields = Array(
                        "ACTIVE" => "Y",
                        "IBLOCK_ID" => $IBLOCK_ID,
                        "NAME" => $SectionCode,
                        "CODE" => $SectionCode,
                );
                $arSection["ID"] = $bs->Add($arSectionFields);
                if(!$arSection["ID"])
                        echo "S:".$bs->LAST_ERROR;
        }

        $arFields["MESSAGE"] = $arFields["ERROR_TEXT_START"]."<font color='red'><span style=\"color: #ff0000;\">".$arFields["ERROR_TEXT_BODY"]."</span></font>".$arFields["ERROR_TEXT_END"];
        $el = new CIBlockElement;
        $arAddFields = Array(
                "IBLOCK_ID"                        =>$IBLOCK_ID,
                "IBLOCK_SECTION"        => $arSection["ID"],
                "NAME"                                => ConvertTimeStamp(time(), "FULL"),
                "ACTIVE"                        => "Y",
                "PREVIEW_TEXT_TYPE"        => "html",
                "PREVIEW_TEXT"                => $arFields["MESSAGE"],
                "DETAIL_TEXT_TYPE"        => "html",
                "DETAIL_TEXT"                => $arFields["MESSAGE"]."<br /><br />\n\n".GetMessage("ALTASIB_ERROR_SEND_COMMENT").":<br />\n".$arFields["COMMENT"]."<br />",
        );
        $arAddFields["PROPERTY_VALUES"]["URL_ERROR"] = $arFields["ERROR_URL"];

        $arAddFields["PROPERTY_VALUES"]["IP_ADDRESS"] = $IP_ADDRESS;
        $ID = $el->Add($arAddFields);

        if(!$ID)
                echo "E: ".$el->LAST_ERROR;
        else
                echo "OK!";

        // to mail
        global $USER;

        $UserLogin = '';
        $UserEmail = '';
        if($USER->IsAuthorized())
        {
                $UserLogin = GetMessage("ALTASIB_ERROR_SEND_USER", array("#LOGIN#" => "[".$USER->GetID()."] (".$USER->GetLogin().") ".$USER->GetFullName()));
                $strEmail = $USER->GetEmail();
                $UserEmail = GetMessage("ALTASIB_ERROR_SEND_EMAIL", array("#EMAIL#" => $strEmail));
        }

        $defEmail = COption::GetOptionString("main", "email_from", "error@".str_replace("www.","",$_SERVER["SERVER_NAME"]));
        $arEventSend = Array(
                "TEXT_MESSAGE"                => $arFields["MESSAGE"],
                "COMMENT_MESSAGE"        => $arFields["COMMENT"],
                "LOGIN"                                => $UserLogin,
                "EMAIL"                                => $UserEmail,
                "URL"                                => $arFields["ERROR_URL"],
                "IP"                                => $IP_ADDRESS,
                "EMAIL_TO"                        => COption::GetOptionString("altasib_errorsend", "email_to", $defEmail, ""),
                "EMAIL_FROM"                => COption::GetOptionString("altasib_errorsend", "email_from", $defEmail, ""),
        );

        $emailTo = COption::GetOptionString("altasib_errorsend", "email_to", "", SITE_ID);

        if(strlen($emailTo) > 0)
                $arEventSend["EMAIL_TO"] = $emailTo;

        if(strlen($strEmail) > 0)
                $emailFrom = $strEmail;
        else
                $emailFrom = COption::GetOptionString("altasib_errorsend", "email_from", "", SITE_ID);

        if(strlen($emailFrom) > 0)
                $arEventSend["EMAIL_FROM"] = $emailFrom;

        CEvent::Send("ALTASIB_ERROR_SEND_MAIL", SITE_ID, $arEventSend);
}
?>

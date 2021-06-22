<?require_once($_SERVER['DOCUMENT_ROOT']. "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->IncludeComponent("bitrix:catalog.compare.list","ajax_count",
Array(
	"AJAX_MODE" => "N",
	"IBLOCK_TYPE" => "catalog",
	"IBLOCK_ID" => "2",
	"DETAIL_URL" => "",
	"COMPARE_URL" => "/compare.php",
	"NAME" => "CATALOG_COMPARE_LIST",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N"
	)
);
?>

<!--  ajax_count -->
<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if(count($arResult) <= 0):?>
	<a href="javascript:void(0)">Сравнение товаров</a> <span>нет товаров</span>
<?else:?>
	<a href="/catalog/compare.php">Сравнение товаров</a> <span>товаров <?=(count($arResult))?></span>
<?endif?>

<!-- вывести количество сравниваемых товаров -->
<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div id="compare_list_count" class="compare cell_block">
	<?if(count($arResult) <= 0):?>
		<a href="javascript:void(0)">Сравнение товаров</a> <span>нет товаров</span>
	<?else:?>
		<a href="/catalog/compare.php">Сравнение товаров</a> <span>товаров <?=(count($arResult))?></span>
	<?endif?>

</div>

<!-- ссылка на добавление к сравнению -->
<a id="compareid_<? echo $arItem["ID"]; ?>" onclick="compare_tov(<? echo $arItem["ID"]; ?>);" href="javascript:void(0)" class="compare">Сравнить</a>

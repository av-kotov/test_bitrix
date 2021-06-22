<?php

// Подключаем нужные модули, если необходимо
\Bitrix\Main\Loader::includeModule('sale');
\Bitrix\Main\Loader::includeModule('catalog');

// Получаем корзину пользователя
$basket = \Bitrix\Sale\Basket::LoadItemsForFUser(
	\Bitrix\Sale\Fuser::getId(),
	SITE_ID
);

// Добавляем товар в корзину, можно добавить несколько товаров, вызвав addProductToBasket для каждого
$product = array('PRODUCT_ID' => 312, 'QUANTITY' => 1);
$result = \Bitrix\Catalog\Product\Basket::addProductToBasket($basket, $product, array('SITE_ID' => SITE_ID));

// Сохраняем корзину в БД.
// Если корзина была взята из заказа $basket = $order->getBasket(), то нужно сохранять заказ, а не корзину.
if (!$result->isSuccess()) {
	var_dump($result->getErrorMessage());
}
$basket->save();

?>

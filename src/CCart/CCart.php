<?php

/**
 * A shopping cart using the session variable
 */
class CCart
{
	/**
	 * Add an item to the shopping cart
	 *
	 * @param stdClass object $item the item to be added to the cart
	 * @param string $redirect the relative url to the page that the browser should redirect to after adding the item
	 * @return result as HTML output
	 */
	public static function addToCart($item, $redirect = null) {
		if (isset($_SESSION['cart'][$item->id])) {
			$output = "<output class='info'>&quot;{$item->title}&quot; is already in shopping cart.</output>";
		}
		else {
			$_SESSION['cart'][$item->id] = $item;
			$output = "<output class='success'>&quot;{$item->title}&quot; added to shopping cart.</output>";
		}
		if ($redirect !== null) {
			$_SESSION['cart_output'] = $output;
			redirect($redirect);
		}
		
		return $output;
	}

	/**
	 * Remove an item from the shopping cart
	 *
	 * @param integer $id id of the item to be removed from the cart
	 * @param string $redirect the relative url to the page that the browser should redirect to after removing the item
	 * @return result as HTML output
	 */
	public static function removeFromCart($id, $redirect = null) {
		unset($_SESSION['cart'][$id]);
		if (self::getNumItems() == 0) {
			self::emptyCart();
		}
		if ($redirect !== null) {
			redirect($redirect);
		}
	}
	
	/**
	 * Remove all items from the shopping cart
	 *
	 * @param string $redirect the relative url to the page that the browser should redirect to after unsetting the cart
	 */
	public static function emptyCart($redirect = null) {
		unset($_SESSION['cart']);
		if ($redirect !== null) {
			redirect($redirect);
		}
	}
	
	/**
	 * Get the total price of shopping cart items
	 *
	 * @return the sum total
	 */
	public static function getTotal() {
		$total = 0;
		if (isset($_SESSION['cart'])) {
			foreach ($_SESSION['cart'] as $item) {
				$total += $item->price;
			}
		}		
		return number_format($total, 2);
	}
	
	/**
	 * Get number of items in shopping cart
	 *
	 * @return number of items
	 */
	public static function getNumItems() {
		return isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
	}
	
	/**
	 * Get a HTML table summary of all items in the shopping cart
	 *
	 * @return string of HTML
	 */
	public static function getCartTable() {		
		if (isset($_SESSION['cart'])) {
			$total = self::getTotal();
			$content = "";
			foreach ($_SESSION['cart'] as $item) {
				$content .= <<<EOD
<tr>
	<td><img src='' alt='[Image path not set]' /></td>
	<td>{$item->title}</td>
	<td>{$item->price}</td>
	<td><a href='?remove-from-cart={$item->id}' class='button-tiny'>&#10007; Remove</a></td>
</tr>
EOD;
			}
			return <<<EOD
<table class='rowlink'>
	<tr>
		<th></th>
		<th>Title</th>
		<th>Price</th>
		<th></th>
	</tr>
	{$content}
	<tr class='sum'>
		<td></td>
		<td style='text-align:right'>Total:</id>
		<td><b>\${$total}</b></id>
		<td><a href='?empty-cart' class='button-tiny' onclick="return confirm('Do you really want to remove all items from the shopping cart?')">&#10007; Empty cart</a></id>
	</tr>
</table>
<p class='center'><a href='' class='button'>Check out</a></p>
EOD;
		}
		else {
			return "<p class='italic'>Your cart is empty.</p>";
		}
	}
}
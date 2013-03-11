<?php 
/** @package Aquarius.shop
  */

/** Format a currency value
  *
  * Params:
  *   1: Toggle currency code display (default true)
  *
  * Example:
  *   {12345|shop_currency_format} => "CHF 12345"
  *   {"123.45"|shop_currency_format:false} => "123.45"
  */
function smarty_modifier_shop_currency_format($value, $show_code=true) {
    $value = Shop::parse_currency($value);
    list($thalers, $cents, $negative) = Shop::parse_currency($value, true);

    $code = defined('SHOP_CURRENCY_CODE')?SHOP_CURRENCY_CODE:'CHF';
    $cent_digits = intval(defined('SHOP_CENT_DIGITS')?SHOP_CENT_DIGITS:2);

    $sign = $negative? '-' : '';

    $str = $sign.(string)$thalers;
    if ($cent_digits > 0) $str .= sprintf(".%0{$cent_digits}d", $cents);
    if ($show_code) $str = $code." ".$str;
    return $str;
}
?>
<?php 
/** @package Aquarius.frontend
  */

/** 
  * This plugin transforms an integer or double value to a curreny value
  * These parameters may or must be given:
  * - value: the money value. if there is a point in it, it will be used as a decimal delimiter. 
  *   if there is no point it will be taken as a integer representing the cents
  * - round: if rounding should be "up", "down" or "normal" (dividing in the middle)
  * - margin: the margin between two possible values (e.g. 5 in switzerland, 1 in euroland)
  */
function smarty_function_currencytransform($params, &$smarty) {
    Log::debug("currency shall be transformed...:");

    $value = str(get($params,"value",false));
    if (!$value) return;
    $round = get($params,"round","normal");
    $minmargin = get($params,"margin",5);

    //get value in decimal notation (with two internal decimal places)
    $values = explode(".",$value);
    if (is_array($values) && count($values) == 2) {
        $result = round($value,2);
    } elseif(is_array($values) && count($values) == 1) {
        //we've got cents!
        $result = ($value.".00");
    } else {
        Log::debug("currencytransform: no correct value was given in ".$value);
        return;
    }

    Log::debug("currency ".$value." transformed: ".$result);
    //round by minmargin


    return $result;
}

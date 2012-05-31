<?php
/** Sort an array randomly */

function smarty_modifier_shuffle($arrData) {
 shuffle($arrData);
   return $arrData;
}

?>
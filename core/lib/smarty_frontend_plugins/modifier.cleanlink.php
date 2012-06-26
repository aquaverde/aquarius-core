<?php
/** Clean links
  * Params:
  *   
  */

require_once "lib/template.lib.php";

function smarty_modifier_cleanlink($link) 
{	
    return clean_link($link);
}
?> 
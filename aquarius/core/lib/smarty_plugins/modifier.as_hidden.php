<?php
/** Write dict as hidden formfields */
function smarty_modifier_as_hidden($formfields) {
    foreach($formfields as $name => $value) {
        $name = htmlspecialchars($name);
        $value = htmlspecialchars($value);
        echo "\n<input type='hidden' name='$name' value='$value'/>";
    }
}
?>
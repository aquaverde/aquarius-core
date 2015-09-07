<?php
/** write Aquarius revision
  *
  * Params:
  *  plain: return string instead of prepared HTML
  */
function smarty_function_show_overrides() {
    global $loader;

    echo "<ul style='display: inline;'>";
    foreach($loader->override_classes as $name => $overrides) {
        echo "<li style='display: inline; margin: 0.5em; padding: 0.5em; background-color: green' title='".join(', ', $overrides)."'>$name</li>";
    }
    echo "</ul>";
}
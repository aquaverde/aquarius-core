<?php

function smarty_function_show_overrides() {

    echo "<ul style='display: inline;'>";
    foreach(array('DEBUG', 'DEV', 'STAGING') as $name) {
        if (constant($name)) {
            echo "<li style='display: inline; margin: 0.5em; padding: 0.5em; background-color: green' title='".join(', ', $overrides)."'>$name</li>";
        }
    }
    echo "</ul>";
}
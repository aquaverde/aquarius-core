<?php

function smarty_function_show_overrides() {
    $overrides = array(
        'DEBUG'   => "Directly logging to content, view source to see it.",
        'DEV'     => "Login with any username no password, caching disabled, and domain configuration ignored.",
        'STAGING' => "Domain configuration ignored"
    );
    echo "<ul style='display: inline;'>";
    foreach($overrides as $name => $explains) {
        if (constant($name)) {
            echo "<li style='display: inline; margin: 0.5em; padding: 0.5em; background-color: green' title='$explains'>$name</li>";
        }
    }
    echo "</ul>";
}
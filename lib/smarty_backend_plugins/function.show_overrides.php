<?php

function smarty_function_show_overrides() {
    $overrides = array(
        'DEBUG'   => "Directly logging to content, view source to see it.",
        'DEV'     => "Login with any username no password, caching disabled, and domain configuration ignored.",
        'STAGING' => "Domain configuration ignored"
    );
    echo "<ul style='display: inline; margin-right: 20px;'>";
    foreach($overrides as $name => $explains) {
        if (constant($name)) {
            echo "<li style='display: inline-block; font-size: 11px; color: #03FF00; margin: 0.5em; padding: 6px 10px; background-color: rgba(1, 128, 0, 0.43)' title='$explains'>$name</li>";
        }
    }
    echo "</ul>";
}
<?php
/** Prevent line-breaks for this string.
  * Replaces all whitespace in the string with the No-Break-Space character.
  * Sequences of whitespace are collapsed to a single no-break space.
  */

function smarty_modifier_nobreak($string) {
    $the_holy_no_break_space = ' ';
    return preg_replace('/[\s]+/', $the_holy_no_break_space, $string);
}

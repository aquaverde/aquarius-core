<?php

/** Create a filter that passes nodes the given user has access to
  * Synopsis: accessible for $user */
return function($parser) {
    // Am I just being a pedant by requiring this 'for' word? I can't deny being a
    // pedant about the wrong things. On the other hand I find filters more pleasant
    // to read that way and it allows extension.
    $for = $parser->consume_word();
    if ($for !== 'for') $parser->fail("First argument must be 'for'");

    $user = $parser->consume_word();

    // The class 'NodeFilter_Login_Required' should actually be called 'NodeFilter_Login_Not_Required'
    // which is why this predicate does not have the same name as the filter it's using
    return new NodeFilter_Login_Required($user);
};
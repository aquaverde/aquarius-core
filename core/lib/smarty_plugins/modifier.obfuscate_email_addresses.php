<?php

/** Replace email-addresses in the given HTML markup with a javascript sequence that writes the address when the browser loads the page
  * This prevents spiders from easily harvesting the mail addresses.
  *
  * The browser must have the inhouse js-library with the email() function loaded. Addresses that are not recognized are left as-is, so this modifier should not break things.
  *
  * In case you were wondering, yes, it's an ugly workaround. And it currently doesn't obfuscate domains with non-ASCII characters.
  */
function smarty_modifier_obfuscate_email_addresses($html) {
    // I don't often permit myself the joy of writing stupefyingly large and complicated regex matchers. Since this is an ugly and lost cause anyway, it is a good opportunity to satisfy my base instincts.
    // The first parantheses grab the user part, the second the domain part without TLD, and the last match the TLD.
    return preg_replace_callback(':([-a-z0-9!#$%&*+/=?^_`{|}~.]+)@(([-a-z0-9]+\.)*[-a-z0-9]+)\.([-a-z0-9]+):i', 'obfuscate_email_address', $html);
}

function obfuscate_email_address($parts) {
    $name = $parts[1];
    $domain = $parts[2];
    // The remaining matches before last are the individual domain parts we don't need
    $tld = end($parts);
    $id = uniqid();
    return '<script type="text/javascript">email(\''.$name.'\', \''.$domain.'\', \''.$tld.'\', \''.$id.'\');</script><span id="'.$id.'" />';
}

#!/usr/bin/env php
<?php
// Turn all short PHP tags into long tags
// See http://stackoverflow.com/questions/684587/batch-script-to-replace-php-short-open-tags-with-php
//
// find aquarius/core -name '*.php' -exec aquarius/core/bin/tag_longevity.php {} \;

$content = file_get_contents($argv[1]);
$tokens = token_get_all($content);
$output = '';

foreach($tokens as $tokennr =>$token) {
    if(is_array($token)) {
        list($index, $code, $line) = $token;
        switch($index) {
        case T_OPEN_TAG_WITH_ECHO:
            $output .= '<?php echo ';
            break;
        case T_OPEN_TAG:
            if (strpos($code, '<?php') === 0) {
                // This way we don't lose newlines
                $output .= $code;
            } else {
                $output .= '<?php ';
            }
            break;
        case T_CLOSE_TAG:
            // Last token?
            end($tokens);
            if ($tokennr === key($tokens)) {
                // Omit closing PHP tag at end of files
            } else {
                $output .= $code;
            }
            break;
        default:
            $output .= $code;
            break;
        }
    } else {
        $output .= $token;
    }
}
file_put_contents($argv[1], $output);
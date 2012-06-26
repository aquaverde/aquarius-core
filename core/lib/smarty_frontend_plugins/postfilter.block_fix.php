<?php
/** Ensure that occurrences of 'smarty_block_block(' have 'echo' in front of them.
  * This allows replacing template blocks with already generated content without executing the block. Smarty usually does display text returned from first invocation of blocks. Yes, this here plugin is ugly. */
function smarty_postfilter_block_fix($source, &$smarty) {
    return preg_replace('/(echo )?smarty_block_block\(/', 'echo smarty_block_block(', $source);
}
?> 
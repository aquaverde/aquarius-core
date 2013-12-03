<?php
/** @package Aquarius.frontend */

/** extends prefilter for template inheritance
  * throws away all output and includes extended plugin at the end. See 'block' plugin.
  */
function smarty_prefilter_extends($source, &$smarty) {
    // Replace {extends file.tpl} with {include file="file.tpl"}, make sure it's placed at the end
    // Wrap the rest into ob_start() ... ob_end_clean() so it doesn't show up in the output
    return preg_replace(
        '/.*{extends ([^}]*)}(.*)/s',
        '{php}ob_start();array_unshift($this->_blocks, $this->_blocks[0]);{/php}$2 {php}ob_end_clean();{/php}{include file="$1"}{php}array_shift($this->_blocks){/php}',
        $source
    );
}


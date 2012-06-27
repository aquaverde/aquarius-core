<?php
/** @package Aquarius.frontend */

/** block plugin for template inheritance
  * Define blocks that replace blocks in extended templates.
  * <pre>
  * Params:
  *   name: Name of the block
  * </pre>
  *
  * Example:
  * <code>
  * File skeleton.tpl:
  *   <html>
  *   <head><title>{block name='title'}Basic title{/block}</title></head>
  *   <body>
  *     {block name='body'}To be replaced{/block}
  *   </body>
  *   </html>
  *
  * File base.tpl:
  *   {extends skeleton.tpl}
  *     {block name="body"}
  *       <div id="menu">{block name='menu'}Go <a href="there.php">there</a>{/block}</div>
  *       <div id="content">{block name='content'}To be replaced{/block}</div>
  *       <div id="sidebar">{block name='sidebar'}Sidebar content to be filled{/block}<div>
  *     {/block}
  *
  * File home.tpl:
  *   {extends base.tpl}
  *     {block name='title'}Home{/block}
  *     {block name='content'}<h1>Welcome home</h1>{/block}
  *     {block name='sidebar'}You're at home{/block}
  *     This text is never shown since it's in a extend but not in a block
  *
  * Displaying home.tpl produces:
  *   <html>
  *   <head><title>Home</title></head>
  *   <body>
  *     <div id="menu">Go <a href="there.php">there</a></div>
  *     <div id="content"><h1>Welcome home</h1></div>
  *     <div id="sidebar">You're at home</div>
  *   </body>
  *   </html>
  * </code>
  *
  * If a block is defined in the child template, blocks of the same name in parent templates are ignored and not executed.
  * Note that parent templates are executed after the content of the extend.
  */
function smarty_block_block($params, $content, &$smarty, &$repeat) {
    $block_name = get($params, 'name');
    if (strlen($block_name) < 1) throw new Exception("Missing 'name' parameter");

    if ($repeat) {
        // Before execution
        // Determine whether block with same name was prepared by child template
        if (isset($smarty->_blocks[0][$block_name])) {
            // Don't execute, just return child block's content
            $repeat = false;
            return $smarty->_blocks[0][$block_name]; // Note that smarty does not display text returned from the first invocation of a block function, we require the hacky postfilter_block_fix for this.
        } else {
            Log::debug("Executing template block '$block_name'");
            return "";
        }
    } else {
        // After execution
        // save content under block's name
        $smarty->_blocks[0][$block_name] = $content;

        return $content;
    }
}

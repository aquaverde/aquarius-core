<?
/** Generate <script> tags around block content.
  * Parameters given to this block replace variables in the block:
  *
  *   {js alertstring='Hello Joe'}
  *      alert('$alertstring')
  *   {/js}
  *
  * Is turned into this (CDATA removed not to confuse the PHP parser):
  *
  *     <script language="javascript" type="text/javascript">
  *         alert('Hello Joe')
  *     </script>
  *
  * The main use of these variables comes when adding {literal} tags:
  *
  * {js alertstring='Hello Joe'}{literal}
  *    Event.observe('window', 'load', function() {
  *        alert('$alertstring')
  *    })
  * {/literal}{/js}
  * 
  */
function smarty_block_js($params, $content, &$smarty, &$repeat) {
    if (isset($content)) {
        foreach ($params as $name => $value) {
            $content = str_replace('$'.$name, $value, $content);
        }
        return
'<script language="javascript" type="text/javascript">
/* <![CDATA[ */
'.$content.'
/* ]]> */
</script>';
    }
    return '';
}
?>
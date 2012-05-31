<?
/** A block that is executed for each successful fetch() on the given object */
function smarty_block_whilefetch($params, $content, &$smarty, &$repeat) {
    $object = get($params, 'object');
    $repeat = $object->fetch();
    return $content;
}
?>
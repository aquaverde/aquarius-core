<?
/** Gets the date of the last change in the content
  * Params:
  *   lg: Get last change for specified language (default: curent language), all langs if false
  */
function smarty_function_lastchange($params, &$smarty) {
    $lg = get($params, 'lg', $smarty->get_template_vars('lg'));
    $content = DB_DataObject::factory('journal');
    $content->lg = $lg;
    $content->limit(1);
    $content->orderBy('last_change DESC');
    $found = $content->find();
    if ($found) {
        $content->fetch();
        return $content->last_change;
    } else {
        return 0;
    }
}
?>
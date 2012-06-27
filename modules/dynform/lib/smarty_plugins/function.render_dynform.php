<?
/** @DEPRECATED use insert_dynform_here() instead.
  * This one doesn't work in some caching scenarios. */
function smarty_function_render_dynform($params, $smarty) {
    global $aquarius;
    $dynform_mod = $aquarius->modules['dynform'];
    echo $dynform_mod->render_dynform($params);
}
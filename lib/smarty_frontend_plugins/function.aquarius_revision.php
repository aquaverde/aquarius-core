<?php 
/** Output aquarius revision if available
  * @param full set to true to print the full revision, not just the version tag, preset false */
function smarty_function_aquarius_revision($params) {
	global $aquarius;
    $revision = $aquarius->revision();
    if (!get($params, 'full')) $revision = first(explode('-', $revision));
    return $revision;
}
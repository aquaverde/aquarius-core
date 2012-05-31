<?
/** Load other content fields
  * Params:
  *   node: The thing you want to load fields for
  *   lg: If you wish to use a specific language

  * If the user does not have permission to access the node (access_restriction) or the content could not be loaded, no fields are altered.
  * Example:
  * {loadcontent node="23"}
  *  Look! The title of node 23: {$title}
  */
function smarty_function_loadcontent($params, &$smarty) {
    assign_content_fields($smarty, $params);
}
?>
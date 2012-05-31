<?
require_once 'lib/action.php';

/** <link> to include a css file from templates/css/
  *
  * @param file filename of the stylesheet
*/
function smarty_function_include_css($params, &$smarty) {

    $file = get($params, 'file');

    $file_loader = Action::use_class('file');
    $action = action_file::make('css', $file);
    $url = new Url('admin.php');
    $url->add_param($action);
    
    return '<link rel="stylesheet" type="text/css" href="'.str($url).'" />';
}
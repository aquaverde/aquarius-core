<?php 
/** Include a javascript library
  * Includes a file from the aquarius/templates/js or modules/?/templates/js dirs. Files will be included only once.
  * @param file filename of the library
  * @param multiple optionally the library can be included multiple times. Setting this flag causes the function to ignore whether the library has been included already and does not mark it as included
  * @param lib optional flag to use the lib dir (templates/js/lib), default false
*/
function smarty_function_include_javascript($params, $template) {
    $included = $template->getTemplateVars('included_js');
    if (!is_array($included)) $included = array();

    $file = get($params, 'file');
    $inline = get($params, 'inline', false);
    $multiple = get($params, 'multiple', false);

    $result = '';
    if ($multiple || !in_array($file, $included)) {
        $lib = get($params, 'lib', false);
        $location = $lib ? 'jslib' : 'js';

        $file_loader = Action::use_class('file');
        $action = action_file::make($location, $file);
        $url = new Url('admin.php');
        $url->add_param($action);

        $result = '<script type="text/javascript" src="'.str($url).'"></script>';

        if (!$multiple) {
            $included []= $file;
            $template->assign('included_js', $file);
        }
    }
    return $result;
}
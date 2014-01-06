<?php 
/** Create link to execute an action
  * Params:
  *   action: action object or string
  *
  * Optional flags:
  *   button: build an submit input field instead of GET link (default: false; but if the action is a changeaction it defaults to true)
  *   return: return to the current action after action completion, not available with button (default: true)
  *   show_title: add link text from action's get_title() (default: true)
  *   show_icon: add link image from action's get_icon() (default: true)
  *   new_button: show news button as text (default: false) 
  *   popup: Parameters for a JS popup (Example: "height=600,width=750"). If this is set, onClick JS code to open the action in a popup will be added.
  *   class: Class attribute string for element, default 'button'
  *   title: Override action title or provide one if the action doesn't
  *   icon_placement: Either 'before' or 'after' the title. Default is 'before'.
  *
  * Use of title and icon depends on whether they are provided by the action. If both are available both will be included in the link, icon first. The title of the action will always be used for 'alt' and 'title' attributes of image links.
  *
  * If the action could not be built or there's no text or icon, this function returns the empty string.
  *
  * When building a button, if the action has an icon and show_icon is true, a type="image" input field will be generated.
  *
  * Examples:
  *   {actionlink action="contentedit:123"}
  */

function smarty_function_actionlink($params, &$smarty) {
    require_once $smarty->_get_plugin_filepath('modifier','makeaction');

    $action = smarty_modifier_makeaction(get($params, 'action'));

    if ($action) {
        $popup = isset($params['popup']);
        $url = $smarty->get_template_vars($popup ? 'simpleurl' : 'url')->with_param($action);
        $onclick = '';

        $lastaction = false;
        if (!$popup && get($params, 'return', true)) {
            $lastaction = $smarty->get_template_vars('lastaction');
            if ($lastaction) {
                $url = $url->with_param($lastaction);
            }
        }
        
        $onclick = '';
        if ($popup) {
            $popup_params = get($params, 'popup');
            $onclick = "onClick=\"window.open('$url', '".$action->get_title()."', '$popup_params'); return false;\" ";
        } 
        $show_title = get($params, 'show_title', true);
        $new_button = get($params, 'new_button', false);

        $spectitle = get($params, 'title');
        $title = htmlspecialchars($spectitle?$spectitle:str($action->get_title()));
        $icon = false;
        if (get($params, 'show_icon', true)) $icon = $action->get_icon();
        $icon_placement = get($params, 'icon_placement', 'before');

        $button = get($params, 'button', $action instanceof ChangeAction);
        if ($button) {
            if ($icon) return "<button name='".str($action)."' data-toggle='tooltip' title='".$title."' class='btn btn-link' $onclick><span class='glyphicon glyphicon-".$icon."'></span></button>";
            else return "<input type='submit' name='".str($action)."' value='$title' data-toggle='tooltip' class='btn btn-default' $onclick/>";
        } else if ($new_button) {
            $content = array();
            if ($title) $content[] = $title;
            if (!empty($content)) return "<a href='".str($url)."' $onclick data-toggle='tooltip' title='".$title."' class=\"btn btn-success btn-sm\"><span class='glyphicon glyphicon-plus-sign'></span> ".implode('&nbsp;', $content).'</a>';
        }
        else {
            $content = array();
            /*if ($icon) $content[] = "<img src='$icon' alt='$title' title='$title'/>";*/
            if ($icon) $content[] = "<span class='glyphicon glyphicon-".$icon."'></span>";
            if ($show_title && $title) $content[] = $title;
            if ($icon_placement == 'after') $content = array_reverse($content);
            if (!empty($content)) return "<a href='".str($url)."' $onclick data-toggle='tooltip' title='".$title."'>".implode('&nbsp;', $content).'</a>';
        }
    }
    return '';
}
?>
<?php 
/** Show title of a form-field
 * 
 * If the form-field has a description configured, that name is used. Else, the
 * translation "formfield_$name" is used, where $name is the name of the field.
 * If both the description and the translation are empty or missing, the
 * name of the field is shown. The title is returned HTML-escaped.
 * 
 * Params:
 *   f: formfield to show title for
 */
function smarty_function_formfield_title($params, &$smarty) {
    $f = get($params, 'f');
    if (!is_object($f)) $smarty->trigger_error("Formfield '$f' not valid");

    $description = trim($f->description);
    if (strlen($description) > 0) return htmlspecialchars($description);
    
    $translation_name = 'formfield_'.$f->name;
    $translation = $smarty->get_config_vars($translation_name);
    if (strlen($translation) > 0) return htmlspecialchars($translation);
    
    return htmlspecialchars($f->name);
}

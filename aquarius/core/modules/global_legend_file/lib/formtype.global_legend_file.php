<?php

class Formtype_global_legend_file extends Formtype_File {

    /** Add js code to dynamically load legend when picture changes */
    function pre_contentedit($node, $content, $formtype, $formfield, $valobject) {
        parent::pre_contentedit($node, $content, $formtype, $formfield, $valobject);

        // The bad and the ugly
        $valobject->extra_js_includes []= 'formfield.global_legend_file.js.tpl';
        $valobject->legend_load_action = Action::make('file_ajax_legend', $content->lg);
    }

    /** Save legend into 'file_legend' table and do not pass through to DB */
    function db_set($values, $formfield, $lg) {
        global $DB;

        $values = parent::db_set($values, $formfield);

        $escaped_path = mysql_real_escape_string('/'.$formfield->sup3.'/'.get($values, 'file'));
        $legend = get($values, 'legend');
        if (strlen($legend) == 0) {
            $DB->query("DELETE FROM file_legend WHERE file='$escaped_path' AND lg='$lg'");
        } else {
            $escaped_legend = mysql_real_escape_string($legend);
            $DB->query("REPLACE file_legend SET file='$escaped_path', legend='$escaped_legend', lg='$lg'");
        }

        unset($values['legend']);

        return $values;
    }

    /** Loads legend from 'file_legend' table if available
      * To enable transitioning from the normal file field, normal legends are still used if there is no entry in the 'file_legend' table. */
    function db_get($values, $formfield, $lg) {
        global $DB;

        $values = parent::db_get($values, $formfield, $lg);

        $escaped_path = mysql_real_escape_string(get($values, 'file'));
        $global_legend = $DB->singlequery("SELECT legend FROM file_legend WHERE file='$escaped_path' AND (ISNULL(lg) OR lg='$lg') ORDER BY lg DESC");

        if (strlen($global_legend) > 0) {
            $values['legend'] = $global_legend;
        }

        return $values;
    }
}

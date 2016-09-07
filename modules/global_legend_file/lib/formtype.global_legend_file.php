<?php

class Formtype_global_legend_file extends Formtype_File {
    // On some flaky servers the loading of the legend via AJAX fails often
    // When loding fails, the legend remains empty and the existing legend is deleted when the content is saved
    // Thus the option to just keep the old legend instead
    var $ignore_empty = false;


    /** Add js code to dynamically load legend when picture changes */
    function pre_contentedit($node, $content, $formtype, $formfield, $valobject, $page_requisites) {
        parent::pre_contentedit($node, $content, $formtype, $formfield, $valobject, $page_requisites);
        // Only add the JS when the full field is being loaded.
        if ($content) {
            /* Background to this hack: The legend is loaded dynamically, when a
               file is selected. Now this must not be done when an additional
               row is loaded with action_file_ajax_empty_row. So... how do we
               know who's asking? If only PHP had a COMEFROM statement. (Sorry,
               that was in bad taste.)
            
               It's actually easy. Since the empty_row action does not know what
               content it's rendering for, the $content parameter is empty, and
               we act on this by only adding the JS when $content is passed,
               meaning the full field is being rendered. This is bad on many
               levels and it is set up to fail.
            */
            // The bad and the ugly
            $valobject->extra_js_includes []= 'formfield.global_legend_file.js.tpl';
            $valobject->legend_load_action = Action::make('file_ajax_legend', $content->lg);
        }
    }

    /** Save legend into 'file_legend' table */
    function db_set($values, $formfield, $lg) {
        global $aquarius;

        $values = parent::db_set($values, $formfield);

        $path ='/'.$formfield->sup3.'/'.get($values, 'file');
        $legend = get($values, 'legend');
        if (strlen($legend) > 0 || !$this->ignore_empty) {
            $aquarius->db->query("REPLACE file_legend SET file=?, legend=?, lg=?", array($path, $legend, $lg));
        }

        return $values;
    }

    /** Loads legend from 'file_legend' table if available
      * To enable transitioning from the normal file field, normal legends are still used if there is no entry in the 'file_legend' table. */
    function db_get($values, $formfield, $lg) {
        global $aquarius;

        $values = parent::db_get($values, $formfield, $lg);

        $path = get($values, 'file');
        $global_legend = $aquarius->db->singlequery("SELECT legend FROM file_legend WHERE file=? AND (ISNULL(lg) OR lg=?) ORDER BY lg DESC", array($path, $lg));

        if ($global_legend !== false) {
            $values['legend'] = $global_legend;
        }

        return $values;
    }
}

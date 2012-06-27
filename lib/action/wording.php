<?
abstract class action_wording extends AdminAction {
    
    var $props = array("class", "command");
    
    /** Allows siteadmins */
    function permit_user($user) {
      return $user->isSiteadmin();
    }

    protected function check_lg() {
        if (!db_Languages::validate_code($this->lg, false)) throw new Exception("Invalid language: '$this->lg'");
    }
}

/** Delete a wording entry for all languages */
class action_wording_delete extends action_wording implements ChangeAction {

    var $props = array('class', 'command', 'id');

    function process($aquarius, $post, $result) {
        $words = DB_DataObject::factory('wording');
        $words->keyword = $this->id;
        $words->find();
        while($words->fetch()) $words->delete();
    }
}

/** Save wording strings in POST */
class action_wording_save extends action_wording implements ChangeAction {

    var $props = array('class', 'command', 'lg');

    function process($aquarius, $post, $result) {
        $this->check_lg();
        $wording = get($post, 'wording');
        $words = DB_DataObject::factory('wording');
        $changed = false;
        foreach ( $wording as $key => $translation ) {
            $words->lg = $this->lg;
            $words->keyword = $key;
            $words->translation = $translation;
            $changed |= $words->update();
        }
        if ($changed) $result->touch_region('content');
    }
}

/** Prepare list of wordings for editing */
class action_wording_list extends action_wording implements DisplayAction {

    var $props = array('class', 'command', 'lg');

    function process($aquarius, $request, $smarty, $result) {
        $this->check_lg();
        $orderkey = get($this->params, 0);
        $orderdir = get($this->params, 1);
        if(empty($orderkey)) $orderkey = "keyword";
        if(empty($orderdir)) $orderdir = "ASC";
        $order = $orderkey." ".$orderdir;

        if($orderdir == "ASC") {
            $nextorderdir = "DESC";
            $orderdirpic = "buttons/sort_asc.gif";
        } else {
            $nextorderdir = "ASC";
            $orderdirpic = "buttons/sort_desc.gif";
        }

        // Build list of all wordings and add delete action for each
        $wordings = db_Wording::getAllWordingsByLg($this->lg, $order);
        foreach($wordings as $wording) $wording->delete_action = Action::make('wording', 'delete', $wording->keyword);

        $smarty->assign("current_lg", $this->lg);
        $smarty->assign("languages", db_Languages::getLanguages());
        $smarty->assign("wordings", $wordings);
        $smarty->assign("orderkey", $orderkey);
        $smarty->assign("nextorderdir", $nextorderdir);
        $smarty->assign("orderdirpic", $orderdirpic);
        $result->use_template('wording.tpl');
    }
}


/** Show dialog to import/export wordings */
class action_wording_port extends action_wording implements DisplayAction {

    function get_title() {
        return new FixedTranslation("Export/Import");
    }
    function process($aquarius, $request, $smarty, $result) {
        $smarty->assign('actions', array(
            Action::make('wording', 'import'),
            Action::make('cancel')
        ));
        $result->use_template('wording_port_dialog.tpl');
    }
}

/** Export full list of wordings as CSV
  * First line of exported CSV contains a header with list of exported languages.
  *
  * Remaining lines have the translation key in the first field, the remaining
  * columns list the translations for each language specified in the header.
  *
  * Example:
  *
  *  key,de,fr
  *  my_translation,"meine Übersetzung","mon translation"
  */
class action_wording_export extends action_wording implements SideAction {

    var $props = array('class', 'command', 'select_untranslated');

    function get_title() {
        return new FixedTranslation($this->select_untranslated ? "Export untranslated  wordings" : "Export all wordings");
    }

    function process($aquarius, $request) {
        $lgs = array();
        foreach(db_Languages::getLanguages() as $lang) $lgs []= $lang->lg;

        $translations_per_key_lg = array();
        $translation = new db_Wording();
        $translation->find();
        while($translation->fetch()) {
            $translations_per_key_lg[$translation->keyword][$translation->lg] = $translation->translation;
        }
        uksort($translations_per_key_lg, 'strcasecmp');

        if ($this->select_untranslated) {
            // Include only lines that seem to be untranslated (have same value as key or primary langage)
            $primary_lg = $lgs[0];
            $secondary_lgs = array_slice($lgs, 1);
            foreach($translations_per_key_lg as $key => $trs) {
                $has_untranslated = false;
                foreach($secondary_lgs as $slg) {
                    $has_untranslated |= $trs[$slg] === $trs[$primary_lg];
                    $has_untranslated |= $trs[$slg] === $key;
                }
                if (!$has_untranslated) {
                    unset($translations_per_key_lg[$key]);
                }
            }
        }

        ob_clean();
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="'.$_SERVER['SERVER_NAME']."-words-".date('Y.m.d-H.i').'.csv"');


        $out = fopen("php://output", "w");

        $columns = array_merge(array('key'), $lgs);
        fputcsv($out, $columns);
        foreach($translations_per_key_lg as $key => $t) {
            $row = array($key);
            foreach($lgs as $lg) {
                $row []= $t[$lg];
            }
            fputcsv($out, $row);
        }
    }
}

/** Import wordings from CSV
  *
  * Expects CSV in the format as generated by the export function. Entries will
  * be created or replaced.
  */
class action_wording_import extends action_wording implements ChangeAction {

    function get_title() {
        return new FixedTranslation("Import wordings");
    }

    function process($aquarius, $request, $result) {
        $wording_csv = get($request, 'wording_csv');
    
        $wording_replacements = $this->getcsv($wording_csv);
    
        // first line contains lg information
        $header = array_shift($wording_replacements);
        if (empty($header) || $header[0] !== 'key') {
            $result->add_message(AdminMessage::with_html('warn', 'Imported CSV has invalid header'));
            return;
        }
        
        $imported_langs = array();
        foreach(array_slice($header, 1) as $import_lg) {
            $lang = DB_DataObject::staticGet('db_Languages', $import_lg);
            if (!$lang) {
                $result->add_message(AdminMessage::with_html('warn', "Language code '$imported_lg' unknown"));
                return;
            }
            $imported_langs []= $lang;
        }
        
        // import line by line
        $added_count = array();
        $changed_count = array();
        foreach($wording_replacements as $replacement) {
            foreach($imported_langs as $field_index => $import_lang) {
                $translation = new db_Wording();
                $translation->lg = $import_lang->lg;
                $translation->keyword = $replacement[0];
                $present = $translation->find(true);
                $translation->translation = $replacement[$field_index + 1];
                if ($present) {
                    $changed = $translation->update();
                    if ($changed) {
                        if (!isset($changed_count[$import_lang->lg])) {
                            $changed_count[$import_lang->lg] = 0;
                        }
                        $changed_count[$import_lang->lg] += 1;
                    }
                } else {
                    $translation->insert();

                    if (!isset($changed_count[$import_lang->lg])) {
                        $changed_count[$import_lang->lg] = 0;
                    }
                    $added_count[$import_lang->lg] += 1;
                }
            }
        }

        if (!empty($changed_count)) {
            $result->add_message(AdminMessage::with_html('ok', "Updated wordings: ".json_encode($changed_count)));
        }
        if (!empty($added_count)) {
            $result->add_message(AdminMessage::with_html('ok', "Added wordings: ".json_encode($added_count)));
        }
    }

    // http://php.net/manual/en/function.str-getcsv.php
    function getcsv($input, $delimiter=',', $enclosure='"') {
        $temp=fopen("php://memory", "rw");
        fwrite($temp, $input);
        fseek($temp, 0);
        $r = array();
        while (($data = fgetcsv($temp, 0, $delimiter, $enclosure)) !== false) {
            $r[] = $data;
        }
        fclose($temp);
        return $r;
    }
}


?>
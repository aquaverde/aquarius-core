<?php 
/** Rich Text Edit formtype.
  * This formtype stores HTML code. In the backend CKEditor is used to provide a WYSIWYG Text Editor.
  * sup1 defines the height of the RTE, default is 180, minimum is 50.
  *
  * More toolbars may be added in public_html/admin/fckconfig/fckconfig.js.
  *
  * The RTE field keeps extra 'fidx' entries in the DB that can be used to
  * narrow file reference lookups.
  */
class Formtype_RTE extends Formtype {
    /** Load FCKEditor */
    function pre_contentedit($node, $content, $formtype, $formfield, $valobject, $page_requisites) {
		$page_requisites->add_js_lib('/aquarius/core/vendor/ckeditor/ckeditor/ckeditor.js');
		
		global $aquarius;
		$rte_options = new RTE_options($aquarius->conf('admin/rte'));
        $rte_options['editor_lg'] = db_Users::authenticated()->adminLanguage;
		$rte_options['content_lg'] = $content->lg;

        if($formfield->sup1) {
            $rte_options['height'] = max(intval($formfield->sup1), 50);
        }
        
        $valobject->rte_plugins = $aquarius->conf('admin/rte/plugins');
        $valobject->rte_plugins_list = join(',', array_keys($aquarius->conf('admin/rte/plugins')));
        $valobject->rte_options = $rte_options;
    }


    function db_get($values, $form_field, $lg) {
        // Skip the index fields
        foreach ($values as $key => $value) {
            if ($key === 'fidx') continue;
            return $value;
        }
        return null;
    }

    
    /** Remove text if it consists of empty tags and whitespace only */
    function db_set($val, $formfield, $lg) {
        // Remove trash characters that get dragged in by copy&paste
        $val = str_replace(chr(0x07), "", $val); // ASCII BELL (ASCII 07). Allegedly showed up in text copied from Indesign, Adobe says BEEP.
    
        // See whether there are any important characters
        $plain_text = strip_tags(html_entity_decode($val));
        $plain_text = str_replace(html_entity_decode('&#160;'), '', $plain_text); // Remove nbsps
        $plain_text = trim($plain_text);

        // RTE is considered empty if there are no important characters, no images, horizontal rules or embedded objects
        $empty = strlen($plain_text) == 0;
        $empty = $empty && stripos($val, '<img') === FALSE;
        $empty = $empty && stripos($val, '<hr') === FALSE;
        $empty = $empty && stripos($val, '<embed') === FALSE;
        $empty = $empty && stripos($val, '<object') === FALSE;
        $empty = $empty && stripos($val, '<iframe') === FALSE;
        
        return $empty ? array() : array($val);
    }

    function db_set_field($vals, $formfield, $lg) {
        $entries = array();

        $vals = $this->db_set($vals, $formfield, $lg);

        // Extract filename references from RTE text
        // The index is used to narrow the search space and is allowed to
        // include irrelevant or bogus entries.
        //
        // We expect the filename to be quoted (lookbehind for quotes) and start
        // with a slash (absolute paths but without the domain part)
        $fileattr_matcher = '#(?<=["\'])/[^"\']+#';
        // so these would be extracted:
        //   <a href="/subdir/file.ico">
        //   <iframe src='/pdfs/doc.pdf'>
        //   And she said "/What is this shit?/ /Let them eat cake!/"
        // and these would not:
        //   <a href="https://binggu.example">
        //   Check example "files/example.doc"
        foreach($vals as $val) {
            $entries []= array('rte' => $val);

            preg_match_all($fileattr_matcher, $val, $matches);
            $filenames = $matches[0];
            $basenames = array_filter(array_map('basename', $filenames));

            foreach($basenames as $basename) {
                $entries []= array('fidx' => $basename);
            }
        }
        return $entries;
    } 


    function import($vals, $field, $lg, $idmap) {
        // Convert the transport ID in internal links
        foreach($vals as &$val) {
            foreach($val as &$str) {
                // Ugly begets ugly
                $str = preg_replace_callback(
                    '/<[\s]*a[\s]+href=["\']aquarius-node:([0-9]+)["\']/',
                    function($matches) use ($idmap) {
                        $db_id = $idmap($matches[1]);
                        if (!$db_id) return '<a href=""'; // No id? no link
                        return '<a href="aquarius-node:'.$db_id.'"';
                    },
                    $str
                );
            }
        }
        return parent::import($vals, $field, $lg, $idmap);
    }
}

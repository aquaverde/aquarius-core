<?php 
/** Rich Text Edit formtype.
  * This formtype stores HTML code. In the backend the FCKEditor is used to provide a WYSIWYG Text Editor.
  * sup1 defines the height of the RTE, default is 180, minimum is 50.
  * sup3 may give an alternative toolbar set
  *   default: Basic toolbar
  *   extended: More functions
  *   picutres: With functionality to insert pictures
  *
  * More toolbars may be added in public_html/admin/fckconfig/fckconfig.js.
  */
class Formtype_RTE extends Formtype {
    /** Load FCKEditor */
    function pre_contentedit($node, $content, $formtype, $formfield, $valobject, $page_requisits) {
		$page_requisits->add_js_lib('rte/ckeditor.js');
		
		if($formfield->sup1)
		    $backendRTE = new BackendRTE(db_Users::authenticated()->adminLanguage, $content->lg, $formfield->sup1);
        else
            $backendRTE = new BackendRTE(db_Users::authenticated()->adminLanguage, $content->lg);
        
        $valobject->rte_options = $backendRTE->get_options();
    }

    /** Remove text if it consists of empty tags and whitespace only */
    function db_set($val, $formfield) {
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

	function db_get($values, $form_field) {
		foreach ($values as $value) {		
			return $value;
		}		
	}
}
?>
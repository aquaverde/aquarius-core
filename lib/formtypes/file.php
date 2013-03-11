<?php 

/** The File formtype saves a filename (with optional subdir), and a legend for each entry.
  * The legend is optional. The base path is provided by the form in the sup3 field, but a file might be in a subdir relative to the base dir if subdirs are enabled with sup1 == 1 in the form.
  *
  * Sups:
  *  sup1: Enable subdirectory selection
  *  sup2: Enable direct file selection via dropdown
  *  sup3: Base path
  *
  * After loading from DB the base path is prepended to the filename so that users of the field see the full path.
  *
  * Example:
  * We have a form_field with
  *     sup1: 1
  *     sup3: pictures/content
  *
  * A content_field with
  *     file: site1/bildli.jpg
  *     legend: Bildli for Site1
  *
  * After loading, the field looks like this:
  *     file: /pictures/content/site1/bildli.jpg
  *     legend: Bildli for Site1
  */
class Formtype_File extends Formtype {

    function to_string($values) {
        return get($values, 'file');
    }

    /** Name for upload fields
      * We can't use the standard field[name][index][whatever] convention in file upload fields because the $_FILES array gets confused. Hence we create a non nested name based on the field name and index. */
    private function upload_name($formname, $index) {
        return "upload_{$formname}_{$index}";
    }
    
    /** Load list of subdirs and attributes for selected file */
    function pre_contentedit($node, $content, $formtype, $formfield, $valobject) {
        $basedir = $formfield->sup3.'/';
        
        if($formfield->multi) {
            // Add an empty field at the end
            $valobject->value[] = array();
        } else {
            $valobject->value = array($valobject->value);
        }

        $valobject->show_subdirs = (bool)$formfield->sup1;
        if ($valobject->show_subdirs) {
            $subdirs = get_cached_dirs($basedir);
            foreach($subdirs as $i => $subdir) $subdirs[$i] = substr($subdir, strlen($basedir));
            $valobject->subdirs = $subdirs;
        }

        $valobject->show_file_select = (bool)$formfield->sup2;
        $valobject->file_row_ids = array();
        $valobject->next_id = 0;

        $valobject->popup_action = Action::make(
            'file_select',
            '',
            $basedir,
            $valobject->show_subdirs ? '' : '/',
            '',
            '',
            '',
            ''
        );

        foreach($valobject->value as $count => &$fileval) {
            $publicname = get($fileval, 'file');
            $file = basename($publicname);
            $filevalue = substr($publicname, strlen($basedir)+1);
            $subdir = substr(dirname($publicname), strlen($basedir)+1);
            $dir = $basedir;
            if (!empty($subdir)) $dir = $basedir.$subdir.'/';

            if ($publicname) {
                $fileinfo = Fileinfo::public_file($publicname);
                if ($fileinfo) {
                    $fileval['fileinfo'] = $fileinfo;
                }
            }
            $fileval['subdir_selected'] = !$valobject->show_subdirs || strlen($subdir) > 0;
            $fileval['file'] = $filevalue;
            $fileval['file_label'] = $file;
            $fileval['dir'] = $dir;
            $fileval['subdir'] = $subdir;
            $fileval['htmlid'] = "f".$formfield->id.'_'.$count;
            $fileval['has_legend'] = strlen(get($fileval, 'legend')) > 0;
            $fileval['weight'] = ($count + 1) * 10;
            $fileval['upload_name'] = $this->upload_name($formfield->name, $count);
            $fileval['index'] = $count;
            $fileval['form_name'] = $valobject->formname."[$count]";
            if ($valobject->show_file_select) {
                $fileval['files'] = listFiles($dir);
            }

            $valobject->file_row_ids []= $fileval['htmlid'];
            $valobject->next_id = max($valobject->next_id, $count + 1);
        }
    }
    
    /** Process uploads and adjust other values */
    function post_contentedit($formtype, $field, $value, $node, $content, &$messages) {
        $resultvals = array();
        foreach($value as $count => $fileval) {
            $filename = $fileval['file'];
            $dirname = $filename;

            if (!empty($filename)) {
                $resultvals[intval(get($fileval, 'weight'))] = array(
                    'file' => $dirname,
                    'legend' => get($fileval, 'legend'),
                    'description' => get($fileval, 'description')
                );
            }
        }

        // Ensure fields are sorted by their weighting
        ksort($resultvals);

        if (!$field->multi) return first($resultvals);
        else return $resultvals;
    }
    
    function db_get($values, $form_field) {
        // Prepend file path
        $file = get($values, 'file');
        if (!empty($file)) {
            $values['file'] = '/'.$form_field->sup3.'/'.$file;
        }
        return $values;
    }

    function db_set($values, $form_field) {
        // Maybe remove prependend path.
        $path_prefix = '/'.$form_field->sup3.'/';
        if (strpos($values['file'], $path_prefix) === 0) {
            $values['file'] = substr($values['file'], strlen($path_prefix));
        }

        return $values;
    }
}
?>
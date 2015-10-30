<?php 
class action_filecheck extends AdminAction implements DisplayAction {
    var $props = array("class");

    function permit_user($user) {
      return (bool)$user;
    }

    function get_title() {
        return new FixedTranslation('Check files');
    }

    function process($aquarius, $request, $smarty, $result) {
        require_once "lib/file_mgmt.lib.php";

        $examined_dirs = explode(';', FILE_ROOT_DIRS);

        $file_paths = array();
        foreach($examined_dirs as $dir) {
            $root = $aquarius->root_path.$dir;
            $cutlen = strlen($aquarius->root_path);
            foreach (new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            ) as $path => $file) {
                $file_paths[substr($file, $cutlen)] []= 'fs';
            }
        }

        foreach($aquarius->db->listquery("
            SELECT DISTINCT CONCAT(form_field.sup3, '/', content_field_value.value) AS dirname
            FROM form_field
            JOIN node ON form_field.form_id = node.form_id
            JOIN content ON node.id = content.node_id
            JOIN content_field ON content.id = content_field.content_id
            JOIN content_field_value ON content_field.id = content_field_value.content_field_id
            WHERE (
                    form_field.type IN ('file', 'global_legend_file')
                AND form_field.name = content_field.name
                AND content_field_value.name = 'file'
            )
        ") as $file) {
            $file_paths[$file] []= 'db';
        }

        foreach($aquarius->db->listquery("
            SELECT content_field_value.value
            FROM form_field
            JOIN node ON form_field.form_id = node.form_id
            JOIN content ON node.id = content.node_id
            JOIN content_field ON content.id = content_field.content_id
            JOIN content_field_value ON content_field.id = content_field_value.content_field_id
            WHERE
                form_field.type = 'rte'
            AND form_field.name = content_field.name
        ") as $richtext) {
            $links = array();
            $dom = new DOMDocument();
            $dom->loadHTML($richtext);

            foreach($dom->getElementsByTagName('a') as $link) $links []= $link->getAttribute('href');
            foreach($dom->getElementsByTagName('img') as $link) $links []= $link->getAttribute('src');

            foreach($links as $link) {
                $parts = explode('/', $link);
                if ($parts[0] === '' && in_array($parts[1], $examined_dirs)) {
                    $file_paths[substr($link, 1)] []= 'db';
                }
            }
        }
        $file_paths = array_map(function($occurrences) {
            return join(array_unique($occurrences));
        }, $file_paths);
        ksort($file_paths);

        $smarty->assign("file_paths", $file_paths);

        $result->use_template('filecheck.tpl');
    }
}

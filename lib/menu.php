<?php 

require_once "lib/adminaction.php";

class Menu {
    // icon to show with the action link
    var $icon = "";
    // the action to execute
    var $action = "";
    /** the name of the menu entry, that name should be present in the *.lang files to be translated for each language. If the name is an array the the second field is printf-ed through the first field (which came from the *.lang files */
    var $name  = "";
    //the submenuentries
    var $subentries;

    public function __construct($_name, $_action, $_icon = '', $_subentries = array()) {
        $this->icon = $_icon;
        $this->action = $_action;
        $this->name = $_name;
        $this->subentries = $_subentries;
    }

    /** Build a menu structure (this should be done somwhere else) */
    static function make($lg) {
        global $aquarius;
    
        $menu = new Menu('menu_root', false, false, array(
            '10' => new Menu('menu_inventory', false, false, array(
                '10' => new Menu('menu_sitemap', Action::make('nodetree', 'show', $lg, 'sitemap'), "picts/node_sitemap_nav.gif"),
                '50' => new Menu('special:nodetree', false),
                '60' => new Menu('special:separator', false),
                '65' => new Menu('lastchanges_menu', Action::make('lastchanges'))
            )),
            '20' => new Menu('menu_filemgr', false, false, array(
                '10' => new Menu('menu_filemgr_browse',        Action::make('filemgr', DEFAULT_MANAGER_STYLE, false, false)),
                '20' => new Menu('menu_filemgr_upload',        Action::make('filemgr', 'upload', false, false)),
                '30' => new Menu('menu_filemgr_dir_settings',  Action::make('dir_settings', 'edit')),
                '40' => new Menu(false,                        Action::make('dir_settings', 'cache_dirs_dialog'))
            )),
            '30' => new Menu('menu_config', false, false, array(
                '10' => new Menu('menu_config_cmsuser',        Action::make('user', 'showList', false)),
                '20' => new Menu('menu_config_wording',        Action::make('wording', 'list', $lg)),
                '30' => new Menu('menu_config_feuser', false, false, array(
                    '10' => new Menu('menu_config_feuser_user', Action::make('feuser', 'list', 'null', '0')),
                    '20' => new Menu('menu_config_feuser_group', Action::make('fegroup', 'list', 'null', '0'))
                ))
            )),
            '50' => new Menu('menu_modules', false, false),
            '60'  => new Menu('menu_super', false, false, array(
                '10' => new Menu('menu_super_site_structure', false, false, array(
                    '10' => new Menu('menu_super_sitemap',     Action::make('nodetree', 'show', $lg, 'super')),
                    '20' => new Menu('menu_super_forms',       Action::make('formlist', 'show')),
                    '25' => new Menu(false,                    Action::make('formfield', 'list')),
                    '30' => new Menu(false,                    Action::make('fieldgroup', 'overview'))
                )),
                '50' => new Menu('menu_super_langs',           Action::make('languageadmin', 'list', $lg)),
                '60' => new Menu('menu_super_modules',         Action::make('modules','list', '')),
                '70' => new Menu(false,                        Action::make('message_edit', 'list')),
                '80' => new Menu('menu_super_tools', false, false, array(
                    '10' => new Menu('menu_super_createcontent', Action::make('create_content', 'show', $lg)),
                    '30' => new Menu(false,                    Action::make('db_maintenance', 'dialog')),
                    '35' => new Menu(false,                    Action::make('pack', 'dialog')),
                    '40' => new Menu(false,                    Action::make('cache_cleaner', 'dialog')),
                    '50' => new Menu(false,                    Action::make('search_replace', 'search', $lg)),
                    '60' => new Menu(false,                    Action::make('fileops', 'center')),
                    '70' => new Menu(false,                    Action::make('maintenance_mode', 'dialog')),
                    '80' => new Menu(false,                    Action::make('echo_cookie', 'edit'))
                )),
                '90' => new Menu('menu_super_links', false, false)
            ))
        ));

        // Add menu link entries from config
        foreach($aquarius->conf('admin/menu_links', array()) as $link) {
            $allowed_status = get($link, 'allow', 1);
            $user = db_Users::authenticated();
            if ($user->status <= $allowed_status) {
                $menu->add_entry($link['parent'], false, new Menu(
                    false, new MenuLink(get($link, 'title'), get($link, 'url'), get($link, 'target'))
                ));
            }
        }

        // Let other pieces add their stuff to the menu
        $aquarius->execute_hooks('menu_init', $menu, $lg);

        // Purge empty
        $menu->clean_menu();

        return $menu;
    }
    
    /** Remove all empty entries from menu tree */
    private function clean_menu() {
        // Find subentries that are not empty
        $nonempty = array();
       
        if($this->subentries != false) {
          foreach ($this->subentries as $order => $entry) {
              // Tell the subentry to clean up first
              $entry->clean_menu();
              
              // Retain only entries that: contain an action or subentries or are 'special'
              if (
                  $entry->action
                  || count($entry->subentries)
                  || (is_string($entry->name) && substr($entry->name, 0, 7) == "special")
              ) {
                  $nonempty[$order] = $entry;
              }
          }
        }

        // Order the entries according to key
        ksort($nonempty);

        // Replace entries that are not empty as subentries
        $this->subentries = $nonempty;
    }

    /** Find an entry named $name, may be this entry itself or a subentry.*/
    function get_entry($name) {
        if ($this->name == $name) {
            return $this;
        } else {
            foreach($this->subentries as $entry) {
                $found = $entry->get_entry($name);
                if ($found) return $found;
            }
        }
        return false;
    }

    /** Add the given menu entry as subentry of the the specified parent */
    function add_entry($parent_name, $weight, $entry) {
        $parent_entry = $this->get_entry($parent_name);
        if ($parent_entry) {
            if ($weight === false) $weight = 10 + array_pop(array_keys($parent_entry->subentries));
            $parent_entry->subentries[$weight] = $entry;
            return true;
        } else {
            Log::warn("Parent entry '$parent_name' not found, menu entry not added");
            return false;
        }
    }

    /** Get action of this entry or action of the first child entry
      * @return action of this entry or of its first child, null if there is no action */
    function get_action() {
        if ($this->action) return $this->action;
        else if (count($this->subentries) > 0) return first($this->subentries)->get_action();
             else return null;
    }
}

class MenuLink {
    function get_title() {
        return str($this->title);
    }
    
    function get_link() {
        return str($this->link);
    }
    
    function get_target() {
        return str($this->target);
    }

    function __construct($title, $link, $target=false) {
        $this->title = $title;
        $this->link = $link;
        $this->target = $target;
    }
}

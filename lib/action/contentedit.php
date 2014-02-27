<?php 
require_once("lib/db/Journal.php");
require_once("lib/db/Fieldgroup_selection.php");

/** Edit content of a node
  * @package Aquarius.backend
  *
  * All commands expect a node id and a language id as parameters.
  */
abstract class action_contentedit extends AdminAction {

    var $props = array('class', 'command', 'node_id', 'lg');
    var $named_props = array('tab', 'form');
    
    /** Allows site admins and users that have permission to edit the node in that language.
      * New nodes may only be created inside boxes.
      */
    function permit_user($user) {
        // Load the node in question (edit permissions are attached to nodes, not content)
        $node = false;
        if ($this->node_id == 'new') {
            $parentid = get($this->params, 0, 0);
            // The parent node must exist
            $parent = DB_DataObject::staticGet('db_Node', $parentid);
            if (!$parent) {
                return false;
            } else {
                $node = new db_Node();
                $node->parent_id = $parentid;
                
                // Node must have at least one available form, else the user is not allowed to create it
                if (!$this->forms_available($parent)) return false;
                
                // User must be able to edit parent to be allowed creating a child
                if (!$user->may_edit($parent)) return false;
            }
        } else {
            // Check that the node exists
            $node = DB_DataObject::staticGet('db_Node', $this->node_id);
            if (!$node) return false;
            
            if (!$user->may_edit($node)) return false;
        }

        // See if we have content
        $content = $node->get_content($this->lg);

        // Commands work only on existing content, except the edit and save commands
        if (!$content && !in_array($this->command, array('edit', 'save'))) return false;

        // We have a loaded node, let's check whether the user has permission to edit it
        if ($user->isSiteadmin()) return true; // Siteadmins can edit everything

        // Ensure user has permission to edit content in given language
        if (!in_array($this->lg, $user->getAccessableLanguages())) return false;

        return true; // E made it
    }

    protected function load_simple() {
        $content = DB_DataObject::factory('content');
        $content->lg = $this->lg;
        $content->node_id = $this->node_id;
        $exists = $content->find(true);
        if (!$exists) throw new Exception("Unable to load content for node ".$content->node_id." lg ".$content->lg);
        return $content;
    }

    /** Load node and content and initialize content
      * This is a bucket for things both edit and save need.
      * @return various loaded stuff, enjoy the hunt */
    protected function load_common() {
        $content = DB_DataObject::factory('content');
        $content->lg = $this->lg;
        $exists = false; // Whether the content we edit is already in the DB
        $node = false;
        
        // Load from DB if there's a valid node
        if (is_numeric($this->node_id)) {
            $content->node_id = $this->node_id;
            $exists = $content->find();
            if ($exists) {
                $content->fetch(); // Load the content if it's there
                $content->load_fields();
            }
            $node = $content->get_node();
            if (!$node) throw new Exception("No node for content '$this->node_id'");
        } elseif ( // Special case for not yet existing nodes
            $this->node_id == 'new'
            && is_numeric($this->params[0])
        ) {
            $parent_id = $this->params[0];
            $parent = db_Node::get_node($parent_id);
            if (!$parent) throw new Exception("Parent node '$parent_id' does not exist");
            
            $node = DB_DataObject::factory('node');
            $node->active = ADMIN_INIT_NODE_ACTIVE;
            $node->parent_id = $parent->id;
        } else throw new Exception("Invalid node id: '$this->node_id'");
        
        $form = false;
        if ($this->form) {
            $form = DB_DataObject::staticGet('db_Form', $this->form);
            if (!$form) throw new Exception("Unable to load form override $this->form");
        } else {
            $form = $node->get_form();
            if (!$form) throw new Exception("Unable to load form for node $this->node_id");
        }
        if (!$form)  throw new Exception("No form for node '$this->node_id'");
        $formfields = $form->get_fields();

        // Default values for new content
        if (!$exists) {
            $content->initialize_properties($formfields);
            if (is_numeric($node->id)) $content->load_language_independent();
        }
        $user = db_Users::authenticated();
        
        return compact('content', 'node', 'exists', 'form', 'formfields', 'user');
    }

    // Whether there are forms available for the given parent node
    function forms_available($parent) {
        // Check for form inherited from parent nodes
        $node = new db_Node();
        $node->parent_id = $parent->id;
        if ($node->box_depth() >= 0) {
            if ($node->get_form()) return true;
        }

        // Check that there's a form available
        $parent_form = $parent->get_form();
        if (!$parent_form) {
            Log::warn("Node ".$parent." has no form!");
            return false;
        }
        return (bool)$parent_form->preset_child();
    }
}

/** Toggle the active flag of the content
  */
class action_contentedit_toggle_active extends action_contentedit implements ChangeAction {
    function process($aquarius, $post, $result) {
        $content = $this->load_simple();

        $content->active = !$content->active;
        $content->update();
        $result->touch_region(Node_Change_Notice::concerning($content->get_node()));
        $result->touch_region('db_dataobject');
        $result->add_message(new Translation('s_message_changed_active'));
    }
}


/** Create a new node selecting from the available forms
  */
class action_contentedit_create extends action_contentedit implements DisplayAction {
    var $props = array('class', 'command', 'parent_id', 'lg');

    function get_title() {
        return new Translation('s_new_child');
    }
    
    function available_forms($parent) {
        // Use the form inherited from the node if available
        $node = new db_Node();
        $node->parent_id = $parent->id;
        if ($node->box_depth() >= 0) {
            $inherited_form = $node->get_form();
            if ($inherited_form) return array($inherited_form);
        }

        // Rely on the parent's form to give us a form
        $parent_form = $parent->get_form();
        return $parent_form->child_forms();
    }

    function permit_user($user) {
        $parent = DB_DataObject::staticGet('db_Node', $this->parent_id);
        if (!$parent) {
            throw new Exception("Unable to load parent node $this->parent_id");
        }

        // Node must have at least one available form, else the user is not allowed to create it
        if (!$this->forms_available($parent)) return false;

        // We have a loaded node, let's check whether the user has permission to edit it
        if ($user->isSiteadmin()) return true; // Siteadmins can edit everything

        // Ensure user has permission to edit content in given language
        if (!in_array($this->lg, $user->getAccessableLanguages())) return false;

        // Finally, ensure that the user has permission to edit its parent
        if (!$user->may_edit($parent)) return false;

        return true; // E made it
    }

    function process($aquarius, $request, $smarty, $result) {
        $parent = DB_DataObject::staticGet('db_Node', $this->parent_id);
        if (!$parent) {
            throw new Exception("Unable to load parent node $this->parent_id");
        }

        $options = array();
        foreach($this->available_forms($parent) as $child_form) {
            $action = Action::build(array('contentedit', 'edit', 'new', $this->lg, $this->parent_id), array('form' => $child_form->id));
            if ($action) {
                $options []= array(
                    'action' => $action,
                    'title' => $child_form->title,
                );
            }
        }

        if (count($options) < 1) {
            return; // Abandon this quest
        }

        if (count($options) == 1) {
            // Jump directly to editing when there is only one option
            $result->inject_action($options[0]['action']);
        } else {
            $smarty->assign('options', $options);
            $result->use_template('choose_template.tpl');
        }
    }
}

/** Prepare a form to edit the content. node_id may be 'new', in that case an additional parameter specifies the parent_id of the node that will be created
  * This action will fill the fields with values from the primary content if the request variable 'copy-primary' is set.
  *
  * Example: contentedit:edit:new:de:12 to add a node as child of node 12 and add german content as well.
  */
class action_contentedit_edit extends action_contentedit implements DisplayAction {

    function get_icon() {
        return 'buttons/edit.gif';
    }

    /** Build a container object and perform formtype specific operations on it before returning it
      */
    static function prepare_container($node, $content, $formfield, $name, $value, $page_requisites) {
        $formtype = $formfield->get_formtype();

        $val = new StdClass();
        $val->htmlid = "f$formfield->id"; // Letter prefix 'f' because XHTML does not allow digits as first character in identifiers
        $val->formname = "field[$formfield->name]"; // Name to be used in <input> tags
        $val->formfield = $formfield;
        $val->name = $name;
        $val->value = $value;
        $val->template_name = $formtype->template_name();
        $val->template_file = 'formfield.'.$val->template_name.'.tpl';

        // Let the formtype add stuff to the container
        $formtype->pre_contentedit($node, $content, $formtype, $formfield, $val, $page_requisites);

        // Convert it to a dictionary, because that's nicer to use in smarty templates
        return object_to_array($val);
    }

    function process($aquarius, $request, $smarty, $result) {
        require_once "lib/file_mgmt.lib.php";
        $page_requisites = new Page_Requisites();
        extract($this->load_common());

        // if requested, replace content with content from primary language
        if (isset($_REQUEST['copy-primary']) && $node->id != "new") {
            $primary_lg = db_Languages::getPrimary()->lg;
            // Only if we aren't editing the primary content
            if ($primary_lg != $content->lg) {
                $primary_content = db_Content::get_cached($node->id, $primary_lg);
                if ($primary_content) {
                    $primary_content->load_fields();
                    // Replace current language content with primary language content but
                    // preserve id and lg attributes
                    $primary_content->id = $content->id;
                    $primary_content->lg = $content->lg;
                    $content = $primary_content;
                }
            }
        }

        // Load grouping selection
        $fieldgroups = false;
        $activeroup = false;
        $firstgroup = false;
        $fieldgrouping = array();
        $fieldgroup_selection = $form->getLink('fieldgroup_selection_id');
        if (!$fieldgroup_selection) {
            $fieldgroup_selection = db_FieldGroup_Selection::standard();
        }
        if ($fieldgroup_selection) {
            $fieldgroups = $fieldgroup_selection->selected_groups();
            $firstgroup = first($fieldgroups);
        }

        $valfields = array();
        foreach($formfields as $formfield) {
            if($formfield->permission_level < $user->status) {
                Log::debug("user is not permitted to edit formfield $formfield->name");
                continue;
            }
            $name = $formfield->name; // Name (title) of this value
            $value = $content->$name; // Current value

            $valfields []= $this->prepare_container($node, $content, $formfield, $name, $value, $page_requisites);

            // Put the fields into the groups that select them
            if ($fieldgroups) {
                $selected = false;
                foreach($fieldgroups as $fieldgroup) {
                    if ($fieldgroup->selects($name)) {
                        $selected = true;
                        $fieldgrouping[$fieldgroup->fieldgroup_id] []= $formfield->name;
                    }
                }
                // Fields that are not selected by any group are put into the first group
                if (!$selected) {
                    if ($firstgroup) {
                        $fieldgrouping[$firstgroup->fieldgroup_id] []= $formfield->name;
                    }
                }
            }
        }


        // Build list of tabs from fields categorized by groups (note that fieldgroups that did not select any fields will not show up here)
        $tabs = array();
        $all_field_names = array_keys($formfields);
        $active_tab_id = false;
        foreach($fieldgrouping as $fieldgroup_id => $fields) {
            $fieldgroup = $fieldgroups[$fieldgroup_id];
            if ($fieldgroup->visibility_level >= $user->status) {
                $show = array_values($fields);
                $hide =  array_values(array_diff($all_field_names, $show));
                $tab = array(
                    'title' => $fieldgroup->title(),
                    'show' => $show,
                    'hide' => $hide,
                    'active' => false,
                    'id' => $fieldgroup_id,
                    'weight' => $fieldgroup->weight
                );
                if ($this->tab == $fieldgroup_id) {
                    $active_tab_id = $fieldgroup_id;
                }
                $tabs [$fieldgroup_id]= $tab;
            }
        }

        // Show tabs if there are at least two
        $active_fields = $all_field_names;
        $active_tab = false;
        if (count($tabs) > 1) {
            // Sort tabs by weight
            require_once "lib/Compare.php";
            uasort($tabs, ArrayCompare::by_entry('weight', 'intcmp'));

            // If no active tab has been selected yet, use the first
            if (!$active_tab_id) {
                $first_tab = first($tabs);
                $active_tab_id = $first_tab['id'];
            }

            $active_tab = &$tabs[$active_tab_id];
            $active_tab['active'] = true;
            $active_fields = $active_tab['show'];

            $smarty->assign('tabs', $tabs);
            $smarty->assign('active_fields', $active_fields);
            $smarty->assign('active_tab_id', $active_tab_id);
        }

        if ($node->id) {
            // Ugly hack to let the user change the form of the node
            $change_form = Action::make('node', 'change_form', $node->id);

            $userper = 0;
            if($user->isSuperadmin()) $userper = 0;
            elseif($user->isSiteadmin()) $userper = 1;
            elseif($user->isUser()) $userper = 2;
            
            $forms = db_Form::get_forms_by_permission($userper);
            $enough = false;
            if(count($forms) > 0) $enough = true;
            
            //CHECK IF USER HAVE PERMISSION ON FORM
            $formperm = false;
            foreach ($forms as $aform) {
                if($aform->id == $form->id) $formperm = true;
            }
            
            $hecan = false;
            if($formperm && $enough) $hecan = true;
            
            if ($hecan) {
                $smarty->assign('change_form', $change_form);
                $smarty->assign('forms', $forms);
                $smarty->assign('is_super', $user->isSuperadmin());
            }

            $last_update = db_Journal::last_update($content);
            $smarty->assign('last_update', $last_update);
            if ($last_update) $smarty->assign('last_user', $last_update->get_user());
        }

        /* Build subtree */
        if ($node->id && !$node->is_content()) {

            $open_nodes = NodeTree::get_open_nodes('contentedit');
            array_unshift($open_nodes, $node->id); // Make sure the current node is in the list of open_nodes
            
            $tree = NodeTree::editable_tree($node, $this->lg, $open_nodes);
            NodeTree::add_controls($tree, $open_nodes, 'sitemap', true, $this->lg);

            if (!empty($tree['children'])) $smarty->assign('entry', $tree);
            $smarty->assign('forallaction', Action::make('nodetree', 'forall'));

            // Maybe collapse contentedit if there are enough children
            $enable_collapse_category = $node->id && !$node->is_content();
            $autocollapse_category = $enable_collapse_category && $aquarius->conf('admin/contentedit/autocollapse_category', false) && (count($tree['children']) >= $aquarius->conf('admin/contentedit/autocollapse_category_children_minimum', 1));

            $smarty->assign('forallaction', Action::make('nodetree', 'forall'));
        }

        // Prepare functions to save and close contentedit
        // Use $this action as template and replace the command with 'save'
        $doneaction = clone $this;
        $doneaction->command = 'save';
        $saveaction = clone $doneaction;
        $saveaction->params[] = 'show';

        $smarty->assign('content', $content);
        $smarty->assign('node', $node);
        $smarty->assign('form', $form);
        $smarty->assign('fields', $valfields);
        $smarty->assign('doneaction', $doneaction);
        $smarty->assign('saveaction', $saveaction);

        // Add a preview URI for existing nodes
        if ($node->id) {
            $uri = $aquarius->frontend_uri_constructor()
                   ->with('lg', $content->lg)
                   ->with('require_active', false)
                   ->to($node);
            $admin_domain = $aquarius->conf('admin/domain');
            if ($admin_domain) $uri->host = $admin_domain;
            $uri->add_param('preview', preview_hash(ECHOKEY));
            $smarty->assign('preview_uri', $uri);
        }

        $smarty->assign('addons', $aquarius->execute_hooks('contentedit_addon', $node, $content, $form));
        $smarty->assign('page_requisites', $page_requisites);

        $result->use_template('contentedit.tpl');
    }

}

/** Save content from form prepared by command edit
  */
class action_contentedit_save extends action_contentedit implements ChangeAction {
    function process($aquarius, $post, $result) {
        extract($this->load_common());
        
        require_once "lib/file_mgmt.lib.php";

        $structural_change = false;

        // Make sure the node remembers what form to use
        $node->form_id = $form->id;

        // Insert new node first of all
        if ($this->node_id == 'new') {
            $node->insert();
            $content->node_id = $node->id; // link
            Log::debug('Inserted new node id '.$node->id.' for new content');
            $structural_change = true;
        } else {
            $node->update();
        }

        $result->touch_region(new Node_Change_Notice($node, $structural_change, false));

        $fieldvals = requestvar("field", array());
        $formtype_messages = array();
        foreach($formfields as $field) {
            if($field->permission_level < $user->status) {
                Log::debug("user has no rights to edit formfield $field->name");
                continue;
            }
            $value = get($fieldvals, $field->name, null);
            $fieldname = $field->name;

            $formtype = $field->get_formtype();
            $value = $formtype->post_contentedit($formtype, $field, $value, $node, $content, $formtype_messages);

            $content->$fieldname = $value;
        }
            
        $content->last_change = time();
        if (is_numeric($content->id)) {
            $content->update();
        } else {
            $content->active = ADMIN_INIT_CONTENT_ACTIVE;
            $content->insert();
        }
        $content->save_content();
                
        db_Journal::content_updated($content);

        // Overwrite node title if this content is the first in the list of languages having content for the node
        foreach(db_Languages::getLanguages() as $lang) {
            if ($this->lg == $lang->lg) {
                $node->title = $content->title();
                $node->update();
                break;
            }
            if ($node->get_content($lang->lg)) {
                // Another language has content for the node
                break;
            }
        }
        
        foreach($formtype_messages as $message) $result->add_message($message);
        $result->add_message(new Translation('s_message_content_updated', array($content->get_title())));

        // Update active tab
        $this->tab = get($post, 'tab', $this->tab);

        // Build an action to show the contentedit form again if this was requested
        if (in_array('show', $this->params)) {
            $result->inject_action(Action::build(array('contentedit', 'edit', $node->id, $this->lg), array('tab' => $this->tab)));
        }
    }
}


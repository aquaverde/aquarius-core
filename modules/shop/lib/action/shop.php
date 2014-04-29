<?php 

class action_shop extends ModuleAction {
    var $modname = "shop";
    var $props = array('class', 'op', 'node','lg','sup1','sup2');

    function valid($user) {
      return (bool)$user;
    }

    function execute() {
        global $aquarius;
        $smarty = false;
        $messages = array();
        $action = false;

        switch($this->op) {
        case "show_admin":
            $smarty = $aquarius->get_smarty_backend_container();
            $paymethods = DB_DataObject::factory('shop_paymethod');
            $smarty->assign("paymethod",$paymethods->get());
            $delivermethod = DB_DataObject::factory('shop_delivermethod');
            $smarty->assign("delivermethod",$delivermethod->get());

            print_r($delivermethod->get());
            print_r($paymethods->get());


            $doneaction = clone $this;
            $doneaction->op = 'save_admin';
            //print_r($doneaction);
            $saveaction = clone $doneaction;
            $saveaction->params[] = 'show_admin';

            $smarty->assign('editcontrolsfunction', editcontrolsfunction());
            $smarty->assign('doneaction', $doneaction);
            $smarty->assign('saveaction', $saveaction);


            $smarty->tmplname = "action.shop_admin.tpl";
            echo "gugus";
            break;
        case "save_admin":
            Log::debug("save admin");
            $get = requestvar('shop');
            Log::debug($get);
            $delivermethod = get($get,'delivermethod',false);
            Log::debug($delivermethod);
            if (get($delivermethod,'save',false)) {
                $db = DB_DataObject::factory('shop_delivermethod');
                foreach($delivermethod as $key => $method) {
                    $db->id = $key;
                    if ($db->find(true)) {
                       Log::debug($db);
                       $db->name = $method["name"];
                       $db->surcharge = $method["surcharge"];
                       $db->update();
                    }
                }
            } elseif(get($delivermethod,'new',false)) {
                $db = DB_DataObject::factory('shop_delivermethod');
                Log::debug("add new");
                Log::debug($db);
                $db->insert();
            }
            break;
        case "edit_attributes":
            $smarty = $aquarius->get_smarty_backend_container();

            $rootid = $this->sup1;
            if (!is_numeric($rootid)) {
                $rootnode = db_Node::get_root();
            } else {
                $rootnode =& DB_DataObject::factory('node');
                $loaded = $rootnode->get($rootid);
                if (!$loaded) throw new Exception("Could not load node for id '$rootid' read from formfield id $this->sup1");
            }

            $nodelist = NodeTree::build_flat($rootnode, array('inactive'));
            $infos = array("nodelist" => $nodelist, "node" => $rootnode, "attrid" => $rootid,"prodid" => $this->node);
            $smarty->assign('shop', $infos);

            // Prepare functions to save and close contentedit
            // Use $this action as template and replace the command with 'save'
            $doneaction = clone $this;
            $doneaction->op = 'save';
            //print_r($doneaction);
            $saveaction = clone $doneaction;
            $saveaction->params[] = 'show';

            $smarty->assign('editcontrolsfunction', editcontrolsfunction());
            $smarty->assign('doneaction', $doneaction);
            $smarty->assign('saveaction', $saveaction);


            $smarty->tmplname = "action.shop_attribute.tpl";

            break;
        case "save":

        $attributes = get(requestvar('shop'), 'attribute', array());

        $files = get($_FILES,'shop',array());
        $attr = array_merge_recursive($attributes,$files);
        //foreach attribute
         foreach($attributes as $attributekey => $attributevalue) {
            //add node mapping
            $node_id1 = $this->node; //content node
            $node_id2 = intval($attributekey);

            $node_mapping = DB_DataObject::factory('node_mapping');
            $node_mapping->node_id1 = $node_id1;
            $node_mapping->node_id2 = $node_id2;
            $node_exists = $node_mapping->find();
            if ($node_exists > 0) $node_mapping->fetch();

            //check if node mapping already exists
            if ($node_exists == 0) {
                $node_mapping->created = time();
                $node_mapping->insert();
            } else {
                $node_mapping->update();
            }

            //add node & content node
            foreach($attributevalue as $choicekey => $choicevalue) {
                if ($node_mapping->id == 0) continue; //what's the problem here
                $content_mapping = DB_DataObject::factory('content_mapping');

                $content_mapping->mapping_id = $node_mapping->id;
                $content_mapping->lg = $this->lg;

                $set = get($choicevalue,'set','');
                if (is_numeric($set)) {
                    $content_mapping->attribute_id = $set;
                } else {
                    $content_mapping->attribute_id = $choicekey;
                }

                $node_exists = $content_mapping->find();
                if ($node_exists > 0) $content_mapping->fetch();


                $radio = false;
                if ($set == "true") {
                    $content_mapping->active = 1;
                } elseif (is_numeric($set)) { //it's a radio button
                    $content_mapping->active = 1;
                    $radio = true;
                } else {
                    $content_mapping->active = 0;
                }

                if ($node_exists > 0) {
                    $content_mapping->update();
                    //echo "update content_c node";
                } else {
                    $content_mapping->insert();
                    //echo "insert content_c node";
                }

                $content_mapping_id = $content_mapping->id;
                Log::debug("content mapping id: ".$content_mapping_id);

                if ($radio) {
                    $content_mapping = DB_DataObject::factory('content_mapping');
                    $content_mapping->mapping_id = $node_mapping->id;
                    $content_mapping->lg = $this->lg;
                    $node_exists = $content_mapping->find();
                    while ($content_mapping->fetch()) {
                        if ($content_mapping->attribute_id != $set) {
                            $content_mapping->active = 0;
                            $content_mapping->update();
                        }
                    }

                }

                //add content mappings
                $fileupload = false;
                if (!empty($_FILES["shop"]["name"][$attributekey][$choicekey]["newfile"])) {
                    Log::debug("fileupload requested");
                    $fileupload = $this->upload_image(SHOP_PICTURE_FOLDER,$attributekey,$choicekey); 
                    Log::debug("file upload return: ".$fileupload);
                    $key = "file";
                }
                foreach($choicevalue as $key => $value) {
                    if ($key == "file" && !is_bool($fileupload)) {
                        $value = $fileupload;
                    }

                    $content_mapping_field = DB_DataObject::factory('content_mapping_field');
                    $content_mapping_field->content_mapping_id = $content_mapping_id;
                    $content_mapping_field->element = mysql_real_escape_string($key);
        
                    $node_exists = $content_mapping_field->find();
                    $content_mapping_field->last_change = time();
                    if ($node_exists > 0) $content_mapping_field->fetch();
                    $content_mapping_field->value = mysql_real_escape_string($value);
                    if ($node_exists > 0) {
                        $content_mapping_field->update();
                    } else {
                        $content_mapping_field->insert();
                    }
                }
            }
         }


            // Build an action to show the contentedit form again if this was requested
            if (in_array('show', $this->params)) {
                $action = Action::make('shop', 'edit_attributes', $this->node, $this->lg, $this->sup1, $this->sup2);
            }
            break;
        default:
            throw new Exception("Operation unknown: '$this->op'");
        }
        return compact('messages', 'smarty','action');
    }

    function upload_image($folder,$attribute_id, $choice_id) {
        require_once "lib/file_mgmt.lib.php";
        $new_name = false; // Holds filename of uploaded files
        $error = false;

        // Process file uploads
        $upload_infos = get($_FILES, "shop");
        if ($upload_infos) {
            $upload_info = array('name' => $upload_infos['name'][$attribute_id][$choice_id]["newfile"],
                'type' =>$upload_infos['type'][$attribute_id][$choice_id]["newfile"],
                'tmp_name' => $upload_infos['tmp_name'][$attribute_id][$choice_id]["newfile"],
                'error' =>$upload_infos['error'][$attribute_id][$choice_id]["newfile"],
                'size' =>$upload_infos['size'][$attribute_id][$choice_id]["newfile"]);
            $upload_result = process_upload($upload_info, $folder);
            $error = $upload_result['error'];

            // Add upload message, Ignoring empty uploads
            if ($error != UPLOAD_ERR_NO_FILE) {
                $messages[] = $upload_result['message'];
            }
            return $upload_result['new_name'];
        }
        return true;
    }
}


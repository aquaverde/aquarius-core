<?
/** @package Aquarius.backend.shop */

/** 
* 
* 
* Block to build a list from child nodes. the given parent node must be the parent node of the product attributes.
* The Block returns the specific product preferences and the stored values for the given 
* product (smarty-variable: $attributes), if a attribute id (attr_id) is given.
*
* Most of the code is copied from the smarty_block_list plugin. please visit this file for more
* documentation
* @see smarty_block_list
*/
function smarty_block_listattr($params, $content, &$smarty, &$repeat) {
    static $lists; // Holds the list of nodes to be shown; as a stack for nested invocations

    $prod_id = get($params, 'prodid');
    $attr_id = get($params, 'attrid',false);
    $childsof = db_Node::get_node(get($params, 'childsof'));

    // On first invocation get elements for list
    if ($repeat) {
        $nodes = db_Node::get_nodes(get($params, 'nodes'));
        $hide = get($params, 'hide');
        $active = db_Node::get_node(get($params, 'active'));
        $lang = get($params, 'lg', $smarty->get_template_vars('lg'));

        // Nodes present in the list
        $list = array();
        
        $activenodes = array();
        if ($active) {
            foreach($active->get_parents(true) as $parent) {
                $activenodes[] = $parent->id;
            }
        }


        // What we don't want
        $prefilter = array('inactive');
        $filter = NodeFilter::create('and', array(
            NodeFilter::create('has_content', $lg),
            NodeFilter::create('active', true),
            NodeFilter::create('not', NodeFilter::create('ids', split(',', $hide)))
        ));
        
        // push nodes given by id
        foreach($nodes as $node) {
            if ($filter->pass($node)) $list[] = $node;
        }
        
        // push childs of a node
        if ($childsof) {
            $list = array_merge($list, $childsof->children($prefilter, $filter));
        } 
        
        //get the attributes for the given product id
        $mapping_node = DB_DataObject::factory('node_mapping');
        $attributes = $mapping_node->get_attribute_selection($prod_id);
        //Log::debug($attributes);

        //store content of the given child parent node
        $contents = $childsof->get_content();
        $contents->load_fields();
    
        // Limit length
        $limit = get($params, 'limit');
        if (is_numeric($limit)) {
            $list = array_slice($list, 0, $limit);
        }
        
        $index = 0;
        
        // Push environemnt on stack
        $lists[] = compact('list', 'index', 'activenodes','attributes','contents');
    }
    
    // Get the current environment
    extract(array_pop($lists));
    
    // Next node on the list
    $node = $list[$index];
    
    // Repeat if there's another node
    $repeat = (bool)$node;
    
    // Change smarty template vars if there's another node to be listed
    if ($repeat) {
        $var = get($params, 'var', 'entry');
        $smarty->assign('last', $index >= count($list) - 1);
        
        $list_content = $node->get_content($lg);
        $list_content->load_fields();

        //if an attribute id is given, get the attribute content
        if ($attr_id) {
            $temp_attr = get($attributes,$contents->attrname, array());
            foreach($temp_attr as $key => $attribute) { 
                if ($attribute["id"] == $list_content->node_id) {
                    $act_attributes = $temp_attr[$key];
                    break;
                }
            }
            if ($contents->pictures) {
                require_once "lib/file_mgmt.lib.php";
                $act_attributes["field"]['folder'] = SHOP_PICTURE_FOLDER;
                $act_attributes["field"]['absolutepath'] = PROJECT_URL.'/'.$act_attributes["field"]['folder'];
                $act_attributes["field"]['files'] = listFiles($act_attributes["field"]['folder']);

                if(strlen($act_attributes['file']) < 1) {
                    $act_attributes["field"]['href'] = false;
                } else {
                    $act_attributes["field"]['href'] = $act_attributes["field"]['absolutepath']."/".$act_attributes['file'];
                    $thumb = file_prefix($act_attributes['file']."/".$act_attributes['file'], 'th_');
                    if (file_exists($act_attributes["field"]['folder'].$thumb)) {
                        $act_attributes["field"]['thumbnail'] = PROJECT_URL.$thumb;
                    } else {
                        $act_attributes["field"]['thumbnail'] = "buttons/".getFileButton($act_attributes['file']);
                    }
                }
                $act_attributes["field"]['htmlid'] = "f".$act_attributes["id"];

                $act_attributes["field"]['legend'] = $act_attributes['legend'];
                $act_attributes["field"]['filename'] = $act_attributes['file'];
            }
        } 
        if (empty($act_attributes["set"])){
            $act_attributes["set"] = $list_content->enabled;
        }
        $smarty->assign("attributes",$act_attributes);

        $smarty->assign($var, $list_content);
        $index++;

        // Put list back on the stack (I'm not sure where PHP works by reference and where it copies the stuff, this may be slow but works certainly)
        $lists[] = compact('list', 'index', 'activenodes','attributes','contents');
    }
    
    return $content;
}
?>
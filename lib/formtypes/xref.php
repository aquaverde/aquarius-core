<?php 
/** Cross references are used to sort content into multiple categories.
  * The categories themselves are nodes too, so that users can easily add to them.
  *
  * Nodes having xref fields require that a parent node has a xref_selection field. What happens then is best explained by example. Say we have this node-tree:
  * ROOT
  *  +-Categories
  *  |  +-Quality
  *  |  |  +-Made in China
  *  |  |  +-New Zealand first choice
  *  |  |  +-Eurotrash
  *  |  +-Colour
  *  |  |  +-Red
  *  |  |  +-Green
  *  |  |  +-Yellow
  *  |  |  +-Black
  *  |  +-Material
  *  |     +-Wood
  *  |     +-Rubber
  *  |     +-Wool
  *  |     +-Leather (Babyseal)
  *  +-Products
  *     +-Gloves
  *     |  +-Iki thump
  *     |  +-Chemo issue
  *     |  +-Slick stranglers
  *     +-Caps
  *     |  +-Desert roof
  *     |  +-Flower mary
  *     |  +-Wannabe pilot
  *     +-Gift cards
  *        +-The gesture counts
  *        +-Bad conscience
  *
  * Now you want to be able to categorize each product. Say you want to be able to say:
  *   "Iki thumps" are of colour "Yellow", of quality "New Zealand first choice", and made of material "Wool". So the natural way of doing things is offering those categories as dropdown selects for each product. So you give them an xref field where those properties are easily selectable. But how does the xref field know which selections to show?
  *
  * Looking at "Gift Cards", there's the problem that the quality and material categories do not apply, but the colour does, because the card "The gesture counts" is red, whereas "Bad Conscience" is black. We do want to show the colour selection dropdown, but not the other two. Here's where the xref_selection field comes in, with it, you can choose which categories are available. xref_selection is nothing else than a multipointing field in "Gloves", "Caps" and "Gift cards". Now in "Gloves", all categories "Quality", "Colour" and "Material" would be selected, whereas for the "Gift cards" node, only "Colour" needs to be selected.
  *
  * When a product such as "Chemo Issue" is to be edited, the embedded xref field looks for an xref_selection fields in the parents. In this case, "Gloves" has a xref_selection field, so this one is used.
  *
  */
class Formtype_Xref extends Formtype {
    /** Apply formtype specific conversion prior to editing content
     */
    function pre_contentedit($node, $content, $formtype, $formfield, $valobject) {
        if (!$formfield->multi) throw new Exception("xref field must always be set to multi, check form '".$formfield->get_form()->title."'");

        $parents = $node->get_parents() ; 
        $parents = array_reverse($parents) ; 
        
        $selection_field = NULL ; 
        $selection_node = NULL ; 
        
        foreach($parents as $parent)
        {
            $form = $parent->get_form() ; 
            $fields = $form->get_fields() ; 
            
            foreach ($fields as $field) 
            {
                if ($field->type == 'xref_selection') 
                {
                    $selection_field = $field ; 
                    $selection_node = $parent ; 
                    break ; 
                } 
            }
            if ($selection_field) break ; 
        }
        
        if (!$selection_field) {
            throw new Exception("xref does not have a xref_selection in its parents") ;     
        }
        
        $content = $selection_node->get_content($content->lg) ; 
        $content->load_fields() ;
        $field_name = $selection_field->name ; 
        $categories = $content->$field_name ; 

        // Pathetic attempt at sorting the categories. Works reliably only with categories that are siblings.
        require_once("lib/Compare.php");
        usort($categories, ObjectCompare::by_field('weight', 'intcmp'));
        
        $xref = array() ; 
        
        foreach ($categories as $cat)
        {
            $cat_item = array() ; 
            $cat_item['node_id'] = $cat->id ; 
            $cat_item['title'] = $cat->title ; 
            $cat_item['entries'] = array() ; 
            $children = $cat->children() ; 
            
            $cat_item['entries'][0] = new WordingTranslation('Bitte wählen..') ; 
            foreach ($children as $child) {
                $cat_item['entries'][$child->id] = $child->title ; 
            }
            $xref[] = $cat_item ; 
        }
        $valobject->xref = $xref ; 
    }
        
    function db_set($value, $form_field) {
        return $value ;
    }

    function db_get_field ($vals, $formfield) {
        $ret = array() ;
        foreach($vals as $val) {
            $node = get($val, 'node');
            $cat = get($val, 'cat');
            if ($node && $cat) {
                $ret[$cat] = $node;
            }
        }
        return $ret;
    }
}
?>
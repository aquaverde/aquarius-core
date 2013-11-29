
{php}

$lg      = $this->get_template_vars("lg");
$node    = $this->get_template_vars("node");
$action  = clone($this->get_template_vars("lastaction"));

$parents = array();
$my_parents = $node->get_parents((bool)$node->id);
foreach ($my_parents as $parent) {
        $tuple = array(	"action" => false, 
                        "title" => $parent->get_contenttitle($lg));
        if (isset($action->node_id)) {
                $action->node_id = $parent->id;
                if ($action->permit()) 
                        $tuple['action'] = clone($action);
        }
        $parents[] = $tuple;
}
$this->assign('path_parents', $parents);


{/php}

<div id="path">
    {foreach from=$path_parents item=i name=my_path}
        {assign var="title" value=$i.title|truncate:50}
        {if $i.action}
            <a href="{url action0=$lastaction action1=$i.action}" title="{$i.title} {#s_edit#}">{$title|strip_tags}</a>
        {else}
            {$title}
        {/if}
        {if not $smarty.foreach.my_path.last} | {/if}
    {/foreach}
</div>
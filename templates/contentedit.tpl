{include file=header.tpl}
{* {include_javascript file='javascript.js'} *}
{include_javascript file='prototype.js' lib=true}

{if $last_update|default:false}
<div class="lastchange">
{#s_last_change#}: {$last_update->last_change|date_format:"%d.%m.%Y %H:%M"}, {$last_user->name}
</div>
{/if}

{include file='path.tpl' lg=$content->lg}

{assign var=title value=$node->get_contenttitle($content->lg)}
{if $title}
    <h1 title="Node-ID: {$node->id} | Content-ID: {$content->id}" data-toggle="tooltip">{$title|strip_tags|truncate:38}
{else}
    <h1 title="Node-ID: {$node->id} | Content-ID: {$content->id}" data-toggle="tooltip">{#s_new_child#}
{/if}
    
{if $node->access_restricted == 1} 
    <span title="{#s_access_restricted#}" class="glyphicon glyphicon-lock" data-toggle="tooltip"></span>
{/if}
</h1>


<div class="clear"></div>

{if $tabs}
    <div style="height: 30px;"></div> {* padding for tabs sticking out *}
{/if}


<div class="toolbox" style="">
    <form name="toolbox" style="display:inline" method="post" action="{url action0=$lastaction}"  enctype="multipart/form-data" accept-charset="UTF-8">
       {if $node->id}
            {contentlanguageedit node=$node->id currentlg=$content->lg return=false assign=langlinks}
            {if $langlinks|@count > 1}
                {#content_switch_language#}:&nbsp;
                {foreach from=$langlinks item=langlink}
                    {$langlink}
                {/foreach}
            {/if}
        {/if}
    </form>
</div> 

<div id="tabbox">



    <div id="outer" style="position: relative">
    {if $tabs}
        {include_javascript file='contentedit.tabs.js'}
        <ul class="tabs">
        {foreach from=$tabs item=tab key=tabnr}
            <li class="tab {if $tab.active}active{/if}" id='tab{$tabnr}' onclick='show_tab({$tabnr}, {$tab.hide|@json}, {$tab.show|@json}, {$tab.id})'>
                {$tab.title|escape}
            </li>
        {/foreach}
        </ul>
    {/if}
        {include file='toolbar.tpl'}	
        <div class="clear"></div>

        <form name="contentedit" id="contentedit-form" method="post" action="{url}" enctype="multipart/form-data">

    {foreach from=$fields key=field_id item=field}
            <div class='contentedit contentedit_{$field.template_name}' id='box{$field.formfield->name}' style="display: {if $field.formfield->name|@in_array:$active_fields}block{else}none{/if}">
 {if $langlinks|default:false|@count > 1}{if $field.formfield->language_independent} <span title="{#s_multi_language#}" class="right_icons glyphicon glyphicon-globe pull-right" data-toggle="tooltip"></span>{/if}{/if}
                    {if $field.formfield->permission_level != 2}<span title="{#s_may_edit#}: {if $field.formfield->permission_level == 1}Siteadmin{elseif $field.formfield->permission_level == 0}Superadmin{/if}" class="right_icons glyphicon glyphicon-user pull-right" data-toggle="tooltip"></span>{/if}
                <label for="{$field.htmlid}" title="{$field.formfield->name}" data-toggle="tooltip">{formfield_title f=$field.formfield}</label>
                {include file=$field.template_file}
            </div>
    {/foreach}
            {action action=$saveaction}
                <input type='hidden' name='tab' value='{$active_tab_id}' id='active_tab_id'/>
                <input type="hidden" name="check" value="{$content->node_id}{$content->lg}"/>
                <input type="submit" name="{$doneaction}" value="{#s_done#}" class="btn btn-primary" />&nbsp;
                <input type="submit" name="{$saveaction}" value="{#s_save#}" class="btn btn-default" id="savebutton"/>&nbsp;
            {/action}
            <input type="submit" class="btn btn-default"  name="" value="{#s_cancel#}"/>
        </form>
    </div>
</div>



{* Show addons *}
{foreach from=$addons item=addon}
    <div>
    {include file=$addon.template data=$addon.data}
    </div>
{/foreach}
<br/>
{if $entry}
    {* Nodetree of children *}
    {include_javascript file='nodetree.js'}

    <link rel="stylesheet" href="css/nodetree.css" type="text/css" />
    <div class="bigbox" id="boxform">
        <div class="bigboxtitle"><h2>{#s_subcontents#}</h2></div>
        <form action="{url action0=$lastaction}" id="nodetree" method="post">
            <div class="nodetree_container no_root" id="nodetree_entry_{$entry.node->id}">
            {include file='nodetree_container.tpl' hide_root=true base=true}
            </div>&nbsp;
            <div class="right">
                <select name="command">
                {foreach from=$forallaction->commands() key=command item=text}
                    <option value="{$command}">{$smarty.config.$text}</option>
                {/foreach}
                </select>&nbsp;
                <button type="submit" name="{$forallaction}" class="btn btn-default btn-xs">OK</button>
            </div>
        </form>
    </div>

    <script type="text/javascript">
        var load_url = '{url escape=false action0=$lastaction action1="nodetree:children:$lg:contentedit"}'
        var move_url = '{url escape=false action0=$lastaction action1="node:moveorder"}'
        var root = document.getElementById("nodetree_entry_{$entry.node->id}")
        var nodetree = new NodeTree(root, load_url, move_url)
    </script>
{/if}


{include file=footer.tpl}
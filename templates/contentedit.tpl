{include file=header.tpl}
{include_javascript file='javascript.js'}
{include_javascript file='prototype.js' lib=true}

{if $last_update}
<div class="lastchange">
{#s_last_change#}: {$last_update->last_change|date_format:"%d.%m.%Y %H:%M"}, {$last_user->name}
</div>
{/if}

{include file='path.tpl' lg=$content->lg}

{assign var=title value=$node->get_contenttitle($content->lg)}
{if $title}
    <h1 title="Node-ID: {$node->id} | Content-ID: {$content->id}">{$title|strip_tags|truncate:38}
{else}
    <h1 title="Node-ID: {$node->id} | Content-ID: {$content->id}">{#s_new_child#}
{/if}
    
{if $node->access_restricted == 1} 
    <img src="buttons/lock_on.gif" alt="{#s_access_restricted#}" title="{#s_access_restricted#}" />
{/if}
</h1>


<div class="clear"></div>

{if $tabs}
    <div style="height: 23px"></div> {* padding for tabs sticking out *}
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
            <div class='contentedit contentedit_{$field.template_name}' id='box{$field.formfield->name}' style="display: {if !$active_fields || $field.formfield->name|@in_array:$active_fields}block{else}none{/if}">
                <label for="{$field.htmlid}" title="{$field.formfield->name}" class="permission_level{$field.formfield->permission_level}{if $langlinks|@count > 1}{if $field.formfield->language_independent} language_independent{/if}{/if}">{formfield_title f=$field.formfield}
                    {if $langlinks|@count > 1}{if $field.formfield->language_independent} <img src="buttons/multilang.gif" alt="{#multi_language#}" title="{#multi_language#}" class="label_img" />{/if}{/if}
                </label>

        {include file=$field.template_file}

            </div>
    {/foreach}
    {strip}
    <div class="clear"></div>
            {action action=$saveaction}
                <input type='hidden' name='tab' value='{$active_tab_id}' id='active_tab_id'/>
                <input type="hidden" name="check" value="{$content->node_id}{$content->lg}"/>
                <input type="submit" name="{$doneaction}" value="{#s_done#}" class="submit"/>
                <input type="submit" name="{$saveaction}" value="{#s_save#}" class="submit" id="savebutton"/>
            {/action}
                <input type="submit" class="cancel submit" name="" value="{#s_cancel#}"/>

        </form>
    {/strip}
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
        {strip}
        <form action="{url action0=$lastaction}" id="nodetree" method="post">
            <div style="padding-left: 0; padding-top: 10px" class="nodetree_container" id="nodetree_entry_{$entry.node->id}">
                {include file='nodetree_container.tpl' hide_root=true}
            </div>&nbsp;
            <div style="text-align: right">
                <select name="command">
                {foreach from=$forallaction->commands() key=command item=text}
                    <option value="{$command}">{$smarty.config.$text}</option>
                {/foreach}
                </select>&nbsp;
                <input type="submit" name="{$forallaction}" value="ok" class="button" />
            </div>
        </form>
        {/strip}
    </div>

    <script type="text/javascript">
        var request_url = '{url escape=false action0=$lastaction action1="nodetree:children:$lg:contentedit"}'
        var nodetree = new NodeTree(request_url)
    </script>
{/if}


{include file=footer.tpl}

{if !$hide_root}
<div class="nodetree_row {if !$entry.node->newaction}nodetree_node{/if}" style="clear:both">
    {if $entry.show_toggle}
        {if $entry.open}
            <img class="nodetree_toggle" src="picts/toggle-open.gif" onclick="this.src='picts/lapse-loading-white.gif'; nodetree.update({$entry.node->id}, { open: 0 })" alt="{#s_close#}"/>
        {else}
            <img class="nodetree_toggle" src="picts/toggle-closed.gif" onclick="this.src='picts/lapse-loading-white.gif'; nodetree.update({$entry.node->id}, { open: 1 })" alt="{#s_open#}"/>
        {/if}
    {/if}
    {if $entry.node->newaction}
        <a href="{url action=$lastaction|default:false action1=$entry.node->newaction}" title="{#s_new_child#}">

            <span class="glyphicon glyphicon-file"></span>
            <span class="glyphicon glyphicon-plus"></span>
        </a>
    {else}
        {assign var=alttitle value="`$entry.title` (`$entry.node->id`)"}
        {assign var=altedittitle value="`$smarty.config.s_edit`: $alttitle"}
        
        <div style="float: right; padding: 0 5px;">
            <div class="dropdown pull-right">
                <a id="dLabel" role="button" data-toggle="dropdown" data-target="#" href="#" title="{#s_node_dropdown#}...">
                    <span class="glyphicon glyphicon-cog"></span><span class="caret"></span>
                </a>
                <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                    <li>
                        {activationbutton action=$entry.actions.toggle_active active=$entry.node->active show_title=true}
                    </li>
                    {assign var=moveorcopy value="node:moveorcopy:`$entry.node->id`:`$lg`"|makeaction}
                    {if $moveorcopy}
                        <li>
                            {actionlink action=$moveorcopy show_title=true}
                        </li>
                    {/if}
                    {if $entry.actions.delete}
                        <li>
                            {actionlink action=$entry.actions.delete show_title=true}
                        </li>
                    {/if}
                    <li class="divider"></li>
                    <li>
                    {if $entry.actions.editprop}
                        <button name="{$entry.actions.editprop}" class="btn btn-link"><span class="glyphicon glyphicon-wrench"></span> {#s_node_properties#}</button>
                    {/if}
                    
                    </li>
                </ul>
            </div>
        </div>

        <div style="float: right; width: 25px; padding-right: 2px; margin-top: -3px;">
            <input type="checkbox" name="selected[{$entry.node->id}]" value="1"/>
        </div>

        <div style="float: right; width: 30px;">
            <span class="glyphicon glyphicon-move" title="{#s_update_weights#}"></span>
        </div>
    
        <div style="float: right; padding-top: 1px; padding-right: 55px;">
            {contentlanguageedit node=$entry.node->id currentlg=$lg}
        </div>


        <span class="nodetree_title{if $entry.node->is_content()} nodetree_title_content{/if}">
        {if $entry.actions.contentedit}
            <a href="{url action0=$lastaction|default:false action1=$entry.actions.contentedit}" title="{$altedittitle}">
                <span class="glyphicon glyphicon-file{if !$entry.node->active} off{/if}"></span>
                <span class="contenttitle{if !$entry.has_content} dim{/if}">{$entry.title|truncate:75}</span>
            </a>
        {else}
            <span class="glyphicon glyphicon-file{if !$entry.node->active} off{/if}"></span>
            {$entry.title|truncate:75}
        {/if}

        {if $entry.node->access_restricted == 1} 
            &nbsp;<span class="glyphicon glyphicon-lock" title="{#s_access_restricted#}" ></span>
        {/if}
        </span>
    {/if}
</div>
{/if}
{if $entry.open}
    <ul class="nodetree_children">
    {foreach from=$entry.children item=childentry}
        <li class="nodetree_entry{if !$childentry@last} nodetree_connection{/if}">
        {if $childentry@last}
            <img class="nodetree_connection join_bottom" src="picts/joinbottom.gif" alt="" />
        {else}
            <img class="nodetree_connection" src="picts/join.gif" alt="" />
        {/if}
            <div class="nodetree_container" {if $childentry.node->id} id="nodetree_entry_{$childentry.node->id}"{/if}>
                {include file="nodetree_container.tpl" entry=$childentry hide_root=false}
            </div>
        </li>
    {/foreach}
    </ul>
{/if}

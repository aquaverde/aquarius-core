{strip}
{if !$hide_root}
<div class="nodetree_row {if !$entry.node->newaction}nodetree_node{/if}" style="clear:both">
    {if $entry.show_toggle}
        {if $entry.open}
            <img class="nodetree_toggle" src="picts/toggle-open.gif" onclick="this.src='picts/lapse-loading-white.gif'; nodetree.update({$entry.node->id}, { open: 0 })" alt="{#s_close#}"
            />
        {else}
            <img class="nodetree_toggle" src="picts/toggle-closed.gif" onclick="this.src='picts/lapse-loading-white.gif'; nodetree.update({$entry.node->id}, { open: 1 })" alt="{#s_open#}"
            />
        {/if}
    {/if}
    {if $entry.node->newaction}
        <a href="{url action=$lastaction|default:false action1=$entry.node->newaction}" title="{#s_new_child#}">
            <img src="picts/node_content_on.gif" alt="{#s_new_child#}" />
            <img src="buttons/new.gif" alt="{#s_new_child#}" />
        </a>
    {else}
        {assign var=alttitle value="`$entry.title` (`$entry.node->id`)"}
        {assign var=altedittitle value="`$smarty.config.s_edit`: $alttitle"}
        <div style="float: right; width: 15px; padding-top: 2px; padding-right: 2px;">
            <input type="checkbox" name="selected[{$entry.node->id}]" value="1"/>
        </div>
        <div style="float: right; width: 15px; padding-top: 4px">
            {activationbutton action=$entry.actions.toggle_active active=$entry.node->active}
        </div>
        <div style="float: right; width: 30px; padding-top: 4px">
            {assign var=moveorcopy value="node:moveorcopy:`$entry.node->id`:`$lg`"|makeaction}
            {if $moveorcopy}
                <input name="{$moveorcopy}" type="image"  title="{#s_move_copy#}" alt="{#s_move_copy#}" src="buttons/move.gif"/>
            {else}
                &nbsp;
            {/if}
        </div>
         <div style="float: right; width: 20px; padding-top: 4px">
            {if $entry.actions.delete}
                {actionlink action=$entry.actions.delete show_title=false}
            {else}
                &nbsp;
            {/if}
        </div>
        <div style="float: right; width: 37px; padding-top: 4px;">
        {if $entry.may_change_weight}
            <input type="text" name="weight[{$entry.node->id}]" value="{$entry.node->weight}" class="inputweight"/>
        {else}
            &nbsp;
        {/if}
        </div>
        {if $entry.actions.editprop}
        <div style="float: right; width: 40px; padding-top: 6px">
            <input name="{$entry.actions.editprop}" type="image" src="picts/editnode.gif" alt="node properties" title="node properties"/>
        </div>
        {/if}
        <div style="float: right; padding-top: 3px; padding-right: 35px;">
            {contentlanguageedit node=$entry.node->id currentlg=$lg}
        </div>
        <div style="float: right; width: 25px; padding-top: 4px;">
            {if $entry.actions.contentedit}
                <input name="{$entry.actions.contentedit}" type="image" src="buttons/edit.gif" title="{$altedittitle}" alt="{$altedittitle}"/>
            {/if}
            &nbsp;
        </div>
        {if $entry.forms}
         <div style="float: right;">
            {foreach from=$entry.forms item=form}
            <div style="float: left; width: 80px; padding-top: 2px;" class="eng">
                {if $form}
                    <a href="{url action0=$form.action action1=$lastaction}" {$form.class} title="{$form.form->title|escape}">
                        {$form.form->title|truncate:15|escape}
                    </a>
                {else}
                    ?!
                {/if}
            </div>
            {/foreach}
        </div>
        {/if}

        <span class="nodetree_title{if $entry.node->is_content()} nodetree_title_content{/if}">
        {if $entry.actions.contentedit}
            <a href="{url action0=$lastaction|default:false action1=$entry.actions.contentedit}" title="{$altedittitle}">
                <img src="picts/{$entry.node->icon()}.gif" alt="{$altedittitle}"/>
                <span class="contenttitle{if !$entry.has_content} dim{/if}">{$entry.title|truncate:75}</span>
            </a>
        {else}
            <img src="picts/{$entry.node->icon()}.gif" alt="alttitle"/>
            {$entry.title|truncate:50}&nbsp;
        {/if}

        {if $entry.node->access_restricted == 1} 
            &nbsp;<img src="buttons/lock_on.gif" alt="{#s_access_restricted#}" title="{#s_access_restricted#}" />
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
            <img class="nodetree_connection" src="picts/joinbottom.gif" alt="" />
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
{/strip}
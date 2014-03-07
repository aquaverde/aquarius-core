
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
        <a href="{url action=$lastaction|default:false action1=$entry.node->newaction}" title="{#s_new_child#}" data-toggle="tooltip">

            <span class="glyphicon glyphicon-file"></span>
            <span class="glyphicon glyphicon-plus"></span>
        </a>
    {else}
        {assign var=alttitle value="`$entry.title` (`$entry.node->id`)"}
        {assign var=altedittitle value="`$smarty.config.s_edit`: $alttitle"}
        <div style="float: right; width: 25px; padding-right: 2px; text-align:right;">
            <input type="checkbox" name="selected[{$entry.node->id}]" value="1"/>
        </div>
        <div style="float: right; width: 25px;">
            {activationbutton action=$entry.actions.toggle_active active=$entry.node->active}
        </div>
        <div style="float: right; width: 25px;">
            {assign var=moveorcopy value="node:moveorcopy:`$entry.node->id`:`$lg`"|makeaction}
            {if $moveorcopy}
                <button name="{$moveorcopy}" class="btn btn-xs btn-link" title="{#s_move_copy#}" data-toggle="tooltip"><span class="glyphicon glyphicon-retweet"></span></button>
            {else}
                &nbsp;
            {/if}
        </div>
         <div style="float: right; width: 25px; padding-top: 1px">
            {if $entry.actions.delete}
                {actionlink action=$entry.actions.delete show_title=false}
            {else}
                &nbsp;
            {/if}
        </div>
        <div style="float: right; width: 60px;">
        {if $entry.may_change_weight}
            <input type="text" name="weight[{$entry.node->id}]" value="{$entry.node->weight}" title="{#s_weight#}" class="inputweight" data-toggle="tooltip"/>
        {else}
            &nbsp;
        {/if}
        </div>
        {if $entry.actions.editprop}
        <div style="float: right; width: 45px; padding-top: 0px">
            <button name="{$entry.actions.editprop}" title="node properties" data-toggle="tooltip" class="btn btn-xs btn-link"><span class="glyphicon glyphicon-wrench"></span></button>
        </div>
        {/if}
        <div style="float: right; padding-top: 1px; padding-right: 55px;">
            {contentlanguageedit node=$entry.node->id currentlg=$lg}
        </div>
        {if $entry.forms}
         <div style="float: right;">
            {foreach from=$entry.forms item=form}
            <div style="float: left; width: 80px; padding-top: 1px;" class="eng">
                {if $form}
                    <a href="{url action0=$form.action action1=$lastaction}" {$form.class} title="{$form.form->title|escape}" data-toggle="tooltip">
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
            <a href="{url action0=$lastaction|default:false action1=$entry.actions.contentedit}" title="{$altedittitle}" data-toggle="tooltip">
                <span class="glyphicon glyphicon-file{if !$entry.node->active} off{/if}"></span>
                <span class="contenttitle{if !$entry.has_content} dim{/if}">{$entry.title|truncate:75}</span>
            </a>
        {else}
            <span class="glyphicon glyphicon-file{if !$entry.node->active} off{/if}"></span>
            {$entry.title|truncate:75}
        {/if}

        {if $entry.node->access_restricted == 1} 
            &nbsp;<span class="glyphicon glyphicon-lock" title="{#s_access_restricted#}" data-toggle="tooltip"></span>
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

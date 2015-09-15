
{if !$hide_root}
    <div class="nodetree_row {if !$entry.node->newaction}nodetree_node{/if}">

        {if $entry.show_toggle}
            <a class="nodetree_toggle {if $entry.open}expand{else}contract{/if} {if $entry.open}open{/if}"></a>
        {/if}

        {if $entry.node->newaction}
            <a href="{url action=$lastaction|default:false action1=$entry.node->newaction}" title="{#s_new_child#}">
                <span class="glyphicon glyphicon-file"></span>
                <span class="glyphicon glyphicon-plus"></span>
            </a>
        {else}
            {assign var=alttitle value="`$entry.title` (`$entry.node->id`)"}
            {assign var=altedittitle value="`$smarty.config.s_edit`: $alttitle"}

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
                &nbsp;<span class="glyphicon glyphicon-lock" title="{#s_access_restricted#}"></span>
            {/if}
            </span>

            <div class="controls">
                <div class="left">
                    <div class="lang">
                        {contentlanguageedit node=$entry.node->id currentlg=$lg}
                    </div>
                </div>
                <div class="right">
                    {if $movable}
                        <div class="move">
                            <span class="glyphicon glyphicon-move" title="{#s_update_weights#}"></span>
                        </div>
                    {/if}
                    <div class="check">
                        <input type="checkbox" name="selected[{$entry.node->id}]" value="1"/>
                    </div>
                    <div class="dropdown pull-right">
                        <a id="dLabel" role="button" data-toggle="dropdown" data-target="#" href="#" title="{#s_node_dropdown#}...">
                            <span class="glyphicon glyphicon-cog"></span>
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                            <li>{activationbutton action=$entry.actions.toggle_active active=$entry.node->active show_title=true}</li>
                            {assign var=moveorcopy value="node:moveorcopy:`$entry.node->id`:`$lg`"|makeaction}
                            {if $moveorcopy}
                                <li>{actionlink action=$moveorcopy show_title=true}</li>
                            {/if}
                            {if $entry.actions.delete}
                                <li>{actionlink action=$entry.actions.delete show_title=true}</li>
                            {/if}
                            <li class="divider"></li>
                            {if $entry.actions.editprop}
                                <li><button name="{$entry.actions.editprop}" class="btn btn-link"><span class="glyphicon glyphicon-wrench"></span>{#s_node_properties#}</button></li>
                            {/if}
                        </ul>
                    </div>
                </div>
            </div>
        {/if}

    </div>
{/if}

{if $entry.open}
    <ul class="{if $base}nodetree_root{else}nodetree_children{/if} {$entry.accepted_children}" data-parent="{$entry.node->id}">
    {foreach from=$entry.children item=childentry}
        <li class="nodetree_container nodetree_entry {if !$childentry@last}nodetree_connection{else}nodetree_plus{/if}" data-form="{$childentry.node->form_id}" data-node="{$childentry.node->id}">
            {include file="nodetree_container.tpl" entry=$childentry hide_root=false base=false}
        </li>
    {/foreach}
    </ul>
{/if}
<ul class="level{$level}">
{foreach from=$menu item='entry'}
    {assign var='name' value=$entry->name}

    {*node tree*}
    {if $name == 'special:nodetree'}
        <li id="nodetree" class="nodetree">
        {include file="navig.nodetree.tpl" nodelist=$entry depth=0}
        </li>

    {elseif $name == "special:separator"}
        <li class="separator"><hr/></li>
    {elseif $name == "special:link"}
        <li class="level"><div class="menu_entry">
        	<a href="$entry->action" target="_self">{#menu_reload#}</a></a>
        	</div></li>
    {else}
        <li class="level{$level}" id="{$name}">
            <div class="menu_entry">
            {assign var="action" value=$entry->get_action()}
            {if $action}
                {if $action|get_class == 'MenuLink'}
                <a href="{$action->get_link()}" {if $action->get_target()} target="{$action->get_target()}"{/if}>
                {else}
                <a href="{url url=$adminurl action=$action}">
                {/if}
                {if $entry->icon != ""}
                    {*<img src="{$entry->icon}" alt="{$smarty.config.$name}" />
                    <span class="glyphicon glyphicon-globe"></span>*}
                {/if}
                {if $entry->action && $entry->action->get_title()}
                    {$entry->action->get_title()|str}
                {else}
                    {$smarty.config.$name}
                {/if}
                </a>
            {else}
                {$smarty.config.$name}
            {/if}
            </div>
            {if $entry->subentries|@count}
                {include file='navig.entry.tpl' menu=$entry->subentries level=$level+1}
            {/if}
        </li>
    {/if}
{/foreach}
</ul>
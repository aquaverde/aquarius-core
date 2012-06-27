
{if $paginator}
{assign var=page_actions value=$paginator->page_actions()}

{if $page_actions|@count > 1}
    <p class="spiner">{#s_spiner_pages#}: &nbsp;

    {assign var=prev value=$paginator->prev_action()}
    {if $prev}
        <a href="{url action0=$lastaction action1=$prev}">&laquo;</a> <span class="light">|</span>
    {/if}

    {foreach from=$page_actions key=page_index item=page_action name=pages}
        <a href="{url action0=$lastaction action1=$page_action}" {if $page_index === $paginator->current_page}style="font-weight:bold; color:orange;"{/if}>
            {$page_index+1}
        </a>
        {if !$smarty.foreach.pages.last}
            <span class="light">|</span>
        {/if}
    {/foreach}

    {assign var=all_action value=$paginator->all_action()}
    {if $all_action}
        <span class="light">|</span>
        <a  {if 'all' === $paginator->current_page}style="font-weight:bold; color:orange;"{/if} href="{url action0=$lastaction action1=$all_action}">
            {#s_filter_reset#}
        </a>
    {/if}

    {assign var=next value=$paginator->next_action()}
    {if $next}
        <span class="light">|</span><a href="{url action0=$lastaction action1=$next}">&raquo;</a> 
    {/if}

    </p>
{/if}
{/if}
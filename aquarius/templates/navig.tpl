<ul class="mainNav">
    {list childrenof=root honor_show_in_menu=true}
        <li>{link node=$item.node class=",on"}{$item.content->title}{/link}
            {loadnodes childrenof=$item.node var=subitems honor_show_in_menu=true}
            {if $subitems|@count}
                <ul class="subNav">
                    {foreach from=$subitems item=subitem name=subnavig}
                        <li>{link node=$subitem.node class=",on"}{$subitem.content->title}{/link}</li>
                    {/foreach}
                </ul>
            {/if}
        </li>
    {/list}
</ul>
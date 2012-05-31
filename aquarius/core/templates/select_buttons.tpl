
        {foreach from=$actions item=action name=button}
            <input type="submit" name="{$action}" class="{if $smarty.foreach.button.last}cancel{else}submit{/if}" value="{$action->get_title()}" />
        {/foreach}

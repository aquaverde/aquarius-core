
        {foreach from=$actions item=action name=button}
            {*<input type="submit" name="{$action}" class="{if $smarty.foreach.button.last}cancel{else}submit{/if}" value="{$action->get_title()}" />*}
            <button type="submit" name="{$action}" class="btn btn-{if $smarty.foreach.button.last}default{else}primary{/if}">{$action->get_title()}</button>
        {/foreach}

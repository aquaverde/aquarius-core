{include file="header.tpl"}

{assign var="saveaction" value="wording:save:$current_lg"|makeaction}

<form action="{url action=$lastaction}" method="post">
    <h1>{#s_wording_admin#}</h1>

    <div id="">
    
		<div class="toolbox2" style="margin-top:30px;">
            {foreach from=$languages item=lang}
                {if $lang->lg == $current_lg}<b>{/if}
                {action action="wording:list:`$lang->lg`"}
                    <a href="{url action0=$action}">{$lang->lg}</a>
                {/action}
                {if $lang->lg == $current_lg}</b>{/if} &nbsp;
            {/foreach}
             <button name="{$saveaction}" title="{#s_save#}" class="btn-link"><span class="glyphicon glyphicon-floppy-disk"></span></button>
        </div>


        {* hidden form button so that hitting enter while editing executes saveaction, not delete action *}
        <input style="display: none" type="submit" name="{$saveaction}" value="{#s_save#}" class="submit" />
        <br/>
        <table class="table" width="100%" border="0" cellspacing="4" cellpadding="0">
        <tr>
            <th align="right">

                {if $orderkey == "keyword"}
                {action action="wording:list:$current_lg:keyword:$nextorderdir"}
                    <img src="{$orderdirpic}" style="vertical-align:middle" alt=""/>
                    <a href="{url action0=$action}">{#s_wording_keyword#}</a>
                {/action}
                {else}
                {action action="wording:list:$current_lg:keyword"}
                    <a href="{url action0=$action}">{#s_wording_keyword#}</a>
                    <img src="picts/spacer.gif" width="7" height="15" style="vertical-align:middle" alt=""/>
                {/action}
                {/if}
            </th>
            <th align="left">
                {if $orderkey == "translation"}
                {action action="wording:list:$current_lg:translation:$nextorderdir"}
                   <img src="{$orderdirpic}" style="vertical-align:middle" alt=""/>
                   <a href="{url action0=$action}">{#s_wording_translation#}</a>
                {/action}
                {else}
                {action action="wording:list:$current_lg:translation"}
                    <a href="{url action0=$action}">{#s_wording_translation#}</a>
                    <img src="picts/spacer.gif" width="7" height="15" style="vertical-align:middle" alt=""/>
                {/action}
                {/if}
            </th>
            <th>&nbsp;</th>
        </tr>
{if !$wordings }
        <tr>
            <td colspan="2">
                    <h3>{#s_no_wordings_found#}</h3>
            </td>
        </tr>
{else}

        {foreach from=$wordings item="word"}
            {strip}
            <tr>
                <td align="right" width="19%">
                    {$word->keyword|escape}
                </td>
                <td width="80%">
                    <input type="text"
                           name="wording[{$word->keyword|escape}]"
                           value="{$word->translation|escape}" style="margin:0;" class="form-control" />
                </td>
                <td width="1%" align="right" nowrap="nowrap">
                    <button name="{$word->delete_action}" title="{#s_delete#}" class="btn-link"><span class="glyphicon glyphicon-trash"></span></button>
                </td>
            </tr>
            {/strip}
        {/foreach}
{/if}
        </table>
        
        <input type="submit" name="{$saveaction}" value="{#s_save#}" class="btn btn-primary" />
        <div class="pull-right"><span class="glyphicon glyphicon-sort"></span> {actionlink action="wording:port"}</div>
        
	</div>
</form>
{include file="footer.tpl"}
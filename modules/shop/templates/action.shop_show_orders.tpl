{include file='header.tpl'}

{include_javascript file='date-functions.js' lib=true}
{include_javascript file='datechooser.js' lib=true}

<script language="javascript">
{literal}
    function clearFilter() {
        document.getElementById("timelimitfrom").value = "";
        document.getElementById("timelimitto").value = "";
        document.getElementById("status").selectedIndex = 0;
        document.getElementById("paid").selectedIndex = 0;
    }

    function print_popup(href) {
        var popup = window.open('', '', 'width=600,height=500,resizable=yes,scrollbars=yes');
        popup.onload = function() { popup.print(); }
        popup.location = href;
        return false;
    }

</script>
<style type="text/css">tr.temporary td { color: #6286a0; font-style: italic;}</style>
<link rel='stylesheet' href='css/datechooser.css' type='text/css' />
{/literal}


<h1>{#shop_orders#}</h1>
<div class="topbar" style="min-width:720px">
    <h3>{#shop_orders#}</h3>
    <form name="orderFilterForm" id="orderFilterForm" method="get" action="">
        {#shop_date_range#}:
        <input type="text" id="timelimitfrom" name="filter_from" value="{if $filters.from}{$filters.from|date_format:'%d.%m.%Y'}{/if}" length="10" style="width: 75px" class="inputsmall" />
        {assign var=format value=$smarty.const.DATE_FORMAT|replace:'%':''}
        <img src="picts/date.gif" onclick="showChooser(this, 'timelimitfrom', 'chooserSpan', 2007, 2050, '{$format}', false);"/>
        <div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; "></div>        
        -
        <input type="text" id="timelimitto" name="filter_to" value="{if $filters.to}{$filters.to|date_format:'%d.%m.%Y'}{/if}" length="10" style="width: 75px" class="inputsmall" />
        <img src="picts/date.gif" onclick="showChooser(this, 'timelimitto', 'chooserSpan', 2007, 2050, '{$format}', false);"/>
        <div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden;"></div>        
        
        &nbsp;
        
        {#shop_status#}
        <select name="filter_status" id="status">
            <option value="all"{if $filters.status == 'all'}selected="selected"{/if}>{#shop_all#}</option>
            <option value="temporary" {if $filters.status == 'temporary'}selected="selected"{/if}>{#shop_temporary#}</option>
            <option value="pending" {if $filters.status == 'pending'}selected="selected"{/if}>{#shop_confirmed#}</option>
        </select>
        
        &nbsp;
        
        {#shop_payment#}
        <select name="filter_paid" id="paid">
            <option value="all"{if $filters.paid == 'all'}selected="selected"{/if}>{#shop_all#}</option>
            <option value="0" {if $filters.paid == '0'}selected="selected"{/if}>{#shop_open#}</option>
            <option value="1" {if $filters.paid == '1'}selected="selected"{/if}>{#shop_paid#}</option>
        </select>

        &nbsp;
        {action action="shop_order:show_orders"}
            &nbsp; <input type="submit" name="{$action}" value="{#s_filter_it#}" class="button" />
            &nbsp; <input type="submit" name="{$action}" value="{#s_filter_reset#}" onclick="clearFilter();" class="button" />
        {/action}
    </form>
</div>
<form name="orderform" id="orderform" action="{url action=$last_filter_action}" method="post">
<table width="100%" cellpadding="3" cellspacing="0" class="table">
    <tr align="left">
            <th>&nbsp;</th>
            <th>#</th>
            <th>{#order_date#}</th>
            <th>{#user#}</th>
            <th>{#order_count_products#}</th>
            <th>{#order_discount#}</th>
            <th>{#order_total_price#}</th>
            <th>{#order_paymethod#}</th>
            <th>{#order_paid#}</th>
            <th>&nbsp;</th>
            <th>&nbsp;</th>
            <th><input type="checkbox" onchange="selectAll(this.form,this)"></th>
    </tr>
    {whilefetch object=$order}
        {assign var="cart_total" value=$order->cart_total()}
        <tr class="hover{if $order->status == 'temporary'} temporary{/if}">
            <td>
                <img src="buttons/eye2.gif" alt="{#s_show#}" onclick="javascript: var style = document.getElementById('table{$order->id}').style; style.display = (style.display == 'none') ? (document.all?'block':'table-row'):'none';" style="cursor: pointer; cursor: hand;"/>
            </td>        
            <td title="{$order->id}">{$order->sequence_nr}</td>
            <td{if $order->status == 'temporary'} temporary{/if} nowrap="nowrap">{$order->order_date|date_format:"%d.%m.%Y %H.%M"}&nbsp;</td>
            {assign var="order_user" value=$order->get_user()}
            <td>{$order_user->name}&nbsp;</td>
            <td>{$order->items|@count}&nbsp;</td>
            <td align="right" nowrap="nowrap">{$cart_total.discount|intval|shop_currency_format}&nbsp;</td>
            <td align="right" nowrap="nowrap">{$cart_total.total|intval|shop_currency_format}&nbsp;</td>
            {assign var="paymethod_node" value=$order->get_paymethod()}
            <td>{if $paymethod_node}{$paymethod_node->get_title()|truncate:20}{/if}&nbsp;</td>
            <td align="right">{if $order->paid == 0}{#s_no#}{else}{#s_yes#}{/if}&nbsp;
                {action action="shop_order:changepaid:`$order->id`"}
                    <input type="image" name="{$action}" src="buttons/refresh.gif" alt="change" class="imagebutton"/>
                {/action}
            </td>
            <td align="right">
                {if $order->status == 'temporary'}
                    {action action="shop_order:confirm:`$order->id`"}
                        <input type="submit" name="{$action}" value="{#shop_confirm#}" class="button" onclick="return confirm('{#shop_confirm_status_change#}')"/>
                    {/action}
                {else}
                    &nbsp;
                {/if}
            </td> 
            <td>
                <a target="_blank" onclick="window.open(this.href, '', 'width=600,height=500,resizable=yes,scrollbars=yes'); return false;" href="{url action="shop_order:print:`$order->id`"}"><img src="picts/print.gif" alt="{#s_print#}"/></a>
            </td>
            <td><input type="checkbox" name="selected_order[]" value="{$order->id}"/></td>
        </tr>

        <tr style="display:none; background-color:white" id="table{$order->id}">
            <td>&nbsp;</td>
            <td colspan="11">
                {assign var='addr' value=$order->address}
                {if $addr}
                    <div class="address" style="background-color:#E0E7EC; width: 175px; padding: 10px; margin: 3px 0 10px 0;">
                        <h3>{#shop_address#} <a href="{url action0=$lastaction action1="fe_address:edit:`$order->address_id`"}"><img src="buttons/edit.gif" alt="{#fe_address_edit#}" title="{#fe_address_edit#}"></a></h3>
                        {if $addr->firma}{$addr->firma|escape}<br/>{/if}
                        {$addr->firstname|escape} {$addr->lastname|escape}<br/>
                        {$addr->address|escape}<br/>
                        {if $addr->country}{$addr->country|escape} - {/if}
                        {$addr->zip|escape} {$addr->city|escape}
                        <p style="padding-top: 6px;">
                        {if $addr->phone}{$addr->phone|escape}<br/>{/if}
                        {if $addr->mobile}{$addr->mobile|escape}<br/>{/if}
                        {$addr->email|escape}
                        </p>
                    </div>
                {/if}
                <h3>{#shop_products#}</h3>
                <table class="table" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 5px;">
                    <tr align="left">
                        <th>#</th>
                        <th>{#product_name#}</th>
                        <th>{#product_code#}</th>
                        <th>{#product_attributes#}</th>
                        <th>{#product_count#}</th>
                        <th>{#product_price#}</th>
                        <th>{#product_total_price#}</th>
                    </tr>
                    {foreach from=$order->items key="index" item="product"}
                    <tr class="nohover">
                        <td>{$index+1}</td>
                        <td>{$product->title}&nbsp;</td>
                        <td>{$product->code}&nbsp;</td>
                        <td>{foreach from=$product->attributes item="attribute"}{$attribute->attribute_name|escape}: {$attribute->value|escape}; {/foreach}&nbsp;</td>
                        <td>{$product->count}&nbsp;</td>
                        <td>{$product->price|intval|shop_currency_format}&nbsp;</td>
                        <td>{$product->total_price()|shop_currency_format}&nbsp;</td>
                    </tr>
                    {/foreach}
                </table>
                
                <h3>{#shop_charges#}</h3>
                <table class="table" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 5px;">
                    <tr align="left" class="nohover">
                        <th>#</th>
                        <th>{#charge_name#}</th>
                        <th>{#charge_price#}</th>
                    </tr>
                    {foreach from=$order->charges key='index' item=charge}
                    <tr class="nohover">
                        <td>{$index+1}</td>
                        <td>{$charge->name}&nbsp;</td>
                        <td>{$charge->value|intval|shop_currency_format}&nbsp;</td>
                    </tr>
                    {/foreach}
                </table>
            </td>
        </tr>
    {/whilefetch}
   
        <tr style="background-color:white">
            <td colspan="12" style="text-align: right">
                <select name="selected_orders_command">
                    <option value="set_paid">{#shop_paid#}</option>
                    <option value="delete">{#shop_delete#}</option>
                </select>
                {action action="shop_order:selected_orders"}<input type="submit" name="{$action}" value="{#shop_change#}" class="button" />{/action}
            </td>
        </tr>

    </table>


    </form>
{include file='footer.tpl'}
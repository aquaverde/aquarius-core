<br />
<form style="display: inline" name="newsletterFilter" method="post" action="{url action1=$lastaction}"  enctype="multipart/form-data">
<div class="topbar">
<h2>{#s_filter#}</h2>
<table width="100%" cellpadding="2" cellspacing="0" border="0">
	<tr>
		<td width="200">{#s_language#}
			<select name="showLangOnly" onChange="document.newsletterFilter.submit()">
				<option value="">{#s_all#}</option>
				
                {foreach from=$available_languages item=lang}
                <option value="{$lang.lg}" {if $smarty.request.showLangOnly == $lang.lg} selected="selected" {/if}>
                    {$lang.name}</option>
                {/foreach}
			
			</select>
		</td>
		<td>
			<?=$s_nl_address_contains?>
			<input type=text name="contains" value="{$smarty.request.contains}">&nbsp;
			<input type="submit" value="{#s_search#}" class="button" />&nbsp;
			<input type="button" value="{#s_nl_show_all#}" class="button" onclick="resetSearchFormAndSubmit()"/>
		</td>
	</tr>
</table>
</div>
</form>

<div class="bigbox" style="margin-top: 15px;">
<h2>{$boxtitle}</h2>
<p style="float:right;">{$totalAddressCount} {#newsletter_addresses#}</p>
{literal}
<script type="text/javascript">
    function selectAll(selector) {
        for ( var i = 0 ; i < document.newsletterForm.length; i++ )
        {
            if (document.newsletterForm[i].type == "checkbox")
            {
                document.newsletterForm[i].checked = selector.checked;
            }
        }
    }
    
    function reOrder(what) {
        document.newsletterForm.orderBy.value = what;
        document.newsletterForm.submit();
    }
    
    function navigChange(fromVal, orderBy) {
        document.newsletterForm.from.value = fromVal;
        document.newsletterForm.orderBy.value = orderBy;
        document.newsletterForm.submit();
    }

    function deleteConfirm() {
        if ( confirm("<?=$s_nl_delte?>") )
            document.newsletterForm.submit();
    }
    
    function showEditAll(id, address, lang, groups )
    {
        var elem = document.getElementById("editAddressBar");

        var x,y;
        if (self.pageYOffset) // all except Explorer
        {
            x = self.pageXOffset;
            y = self.pageYOffset;
        }
        else if (document.documentElement && document.documentElement.scrollTop)
            // Explorer 6 Strict
        {
            x = document.documentElement.scrollLeft;
            y = document.documentElement.scrollTop;
        }
        else if (document.body) // all other Explorers
        {
            x = document.body.scrollLeft;
            y = document.body.scrollTop;
        }
        elem.style.position = "absolute";
        
        if ( posY ) {
            var myPosY  = posY + y;
            elem.style.top = myPosY+"px"; 
        }
        
        elem.style.left = "100px";
        elem.style.display  = "";
    
    
        elem.style.opacity = "1.0";
        elem.style.filter = "alpha(opacity=100)";
        
        document.editAddress.editAddress.value = address;
        document.editAddress.editId.value = id;
        document.editAddress.editLanguage.value = lang;
        deselectAllSubscriptions();
        var g = groups.split(",");
        
        for (var i = 0; i <= g.length; i++) {
            var elem = document.getElementById(g[i]);
            if(elem!=null)
                elem.checked=true;
        }
    }
    
    function showEdit(id, address, lang, active )
    {
        var elem = document.getElementById("editAddressBar");

        var x,y;
        if (self.pageYOffset) // all except Explorer
        {
            x = self.pageXOffset;
            y = self.pageYOffset;
        }
        else if (document.documentElement && document.documentElement.scrollTop)
            // Explorer 6 Strict
        {
            x = document.documentElement.scrollLeft;
            y = document.documentElement.scrollTop;
        }
        else if (document.body) // all other Explorers
        {
            x = document.body.scrollLeft;
            y = document.body.scrollTop;
        }
        elem.style.position = "absolute";
        
        if ( posY ) {
            var myPosY  = posY + y;
            elem.style.top = myPosY+"px"; 
        }
        
        elem.style.left = "100px";
        elem.style.display  = "";
    
    
        elem.style.opacity = "1.0";
        elem.style.filter = "alpha(opacity=100)";
        
        document.editAddress.editAddress.value = address;
        document.editAddress.editId.value = id;
        document.editAddress.editLanguage.value = lang;
        
        if(active == 1) {
            document.editAddress.editActive.checked = true;
        } else {
            document.editAddress.editActive.checked = false;
        }
        
    }
    
    function deselectAllSubscriptions() {
        for ( var i = 0 ; i < document.editAddress.length; i++ )
        {
            if (document.editAddress[i].type == "checkbox")
            {
                document.editAddress[i].checked = false;
            }
        }
    }
    
    function disapear () {
  
        opacity = opacity - 0.1;    
        if (opacity <= 0) {
            document.getElementById("editAddressBar").style.display = "none";   
            document.editAddress.editAddress.value = "";
            document.editAddress.editId.value = "";
            document.editAddress.editLanguage.value = "";    
            window.clearInterval(disapearInterval);
        }
        else {
            var elem = document.getElementById("editAddressBar");
            if ( elem.style.opacity ) {
                elem.style.opacity = opacity;
                elem.style.filter = "alpha(opacity="+ opacity*100+")";
            }
        }
        
        
    }
    
    function discardEdit()
    {
        var elem = document.getElementById("editAddressBar");
        opacity = 1.0;
        disapearInterval = window.setInterval("disapear()", 50);
    }
    
    function Mausklick(myEvent)
    {   
        if (!myEvent)
            myEvent = window.event;

        posY  = myEvent.clientY;
    }
    
    function resetSearchFormAndSubmit() {
        var mForm = document.newsletterFilter;
        
        //mForm.from.value = "";
        //mForm.orderBy.value = "";
        mForm.contains.value = "";
        mForm.submit();
        
    }
    
    
    var disapearInterval;
    var opacity = 1.0;
    var posY;
    
    document.onmouseup = Mausklick;
    
</script>
{/literal}

	<div style="margin-top:10px;"></div>
    <a href="#" onClick="document.getElementById('addAddressBar').style.display = '';return false;" class="new_button" style="padding: 2px 5px;">+ {#newsletter_add_address#}</a>&nbsp;&nbsp;
    {action action="newsletter:export:$newsletterRootNode:$lg"}
            <a href="{url action0=$lastaction action1=$action}" title="{#export_addresses#}" class="button">{#newsletter_export_addresses#}</a>
    {/action}
    <br />
    <div class="topbar" id="addAddressBar" style="display: none; margin-top: 15px; width: 75%;">
    <h3>{#newsletter_add_address#}</strong></h3>
    <form style="display: inline" name="addaddress" method="post" action="{url action0=$lastaction}"  enctype="multipart/form-data">
    <table width="100%" cellpadding="2" cellspacing="0" border="0">
        <tr>
            <td>
                <br/>{#newsletter_add_address_field#}<br/>
                <input type="text" name="newAddress" class="ef" />
                <input type="hidden" name="newId" value="NULL" />                
                
                <br/><br/>
                
                {#newsletter_language#} <br/>
                <select name="newLanguage" >
                {foreach from=$available_languages item=lang}
                    <option value="{$lang.lg}">{$lang.name}</option>
                {/foreach}
                </select>

                 <br/><br/>


                {if $alladdresses == true}
                {#newsletters#}:<br/>
                {foreach from=$nodelist item=available_nl}
                    <input type="checkbox" id="" name="addSubscriptions[]" value="{$available_nl.node->id}">
                    {getfield node=$available_nl.node->id field=title var=nodetitle}
                    {$nodetitle}<br/>
                {/foreach}
                {else}
                <input type="hidden" name="addSubscriptions[]" value="{$newsletter_node->id}"/>
                {/if}

                <br/>

                {action action="newsletter:addaddress:0:$lg"}
                    <input type="submit" name="{$action}" value="{#newsletter_save_address#}" class="button" />
                {/action}
                &nbsp;
                <input type="button" value="{#s_cancel#}" class="button" onclick="document.getElementById('addAddressBar').style.display = 'none';" />
            </td>
        </tr>
    </table>
    </form>
    </div>
   
    
    <div class="topbar" id="editAddressBar" style="display: none;width:400px;">
        
    <strong>{#newsletter_edit_address#}</strong>
    <form style="display: inline" name="editAddress" method="post" action="{url action1=$lastaction}"  enctype="multipart/form-data">
    <table width="100%" cellpadding="2" cellspacing="0" border="0">
        <tr>
            <td>
                <?=$s_nl_address?>
                
                <br/>
                
                <input type="text" name="editAddress" class="ef" />
                <input type="hidden" name="editId" value="NULL" />
                {if $alladdresses == true}
                <input type="hidden" name="editActive" value="1" />
                {else}
                <input type="hidden" name="editSubscriptions[]" value="{$newsletter_node->id}"/>
                {/if}
                
                <br/>
                
                {#newsletter_language#}<br/>
                <select name="editLanguage" >
                {foreach from=$available_languages item=lang}
                    <option value="{$lang.lg}">{$lang.name}</option>
                {/foreach}
                </select>

                <br/><br/>                

                {if $alladdresses == true}
                {#newsletters#}:<br>
                {foreach from=$nodelist item=available_nl}
                    <input type="checkbox" id="{$available_nl.node->id}" name="editSubscriptions[]" value="{$available_nl.node->id}">
                    {getfield node=$available_nl.node->id field=title var=nodetitle}
                    {$nodetitle}<br>
                {/foreach}
                {else}
                    <input type="hidden" name="editSubscriptions[]" value="{$newsletter_node->id}"/>
                    {#newsletter_subscription_active#}
                    <input type="checkbox" name="editActive" id="editActive"/>
                {/if}

                <br/>

                {action action="newsletter:editaddress:0:$lg"}
                    <input type="submit" name="{$action}" value="{#s_save#}" class="button" />
                {/action}
                <input type="button" name="cancelEdit" value="{#s_cancel#}" onclick="discardEdit()" class="button" />


            </td>
        </tr>
    </table>
    </form>
    </div>
    
    <div style="padding: 10px 0;">
    {if $totalAddressCount > $addressesPerPage}
    
        {assign var='pages' value=$totalAddressCount/$addressesPerPage|ceil}
        {section name=pages start=1 loop=$pages+1}
            <a href="{url action0=$lastaction}&page={$smarty.section.pages.index}&showLangOnly={$smarty.request.showLangOnly}&contains={$smarty.request.contains}">
                {if $currentPageNr == $smarty.section.pages.index}<b style="color:orange;">{/if}
                {$smarty.section.pages.index}
                {if $currentPageNr == $smarty.section.pages.index}</b>{/if}</a> <span class="light">|</span>
        {/section}
    
    {/if}
    </div>
    
    <form style="display: inline" name="newsletterForm" method="post" action="{url action1=$lastaction}"  enctype="multipart/form-data">   
    
    <table width="100%" cellpadding="2" cellspacing="0" border="0" class="table-list">
    <tr bgcolor="#e0e7ec">
        <td><input type="checkbox" onChange="selectAll(this)" class="button" />&nbsp;</td>
        <td>&nbsp;</td>
        <td><strong>{#newsletter_address#}</strong></td>
        <td align="center"><strong>{#newsletter_language#}</strong></td>
       {if $alladdresses==true} <td><strong>{#newsletter_subscribed_nls#}</strong></td>
       
        {else}
        <td align="right"><strong>{#newsletter_subscription_date#}</strong></td>
        <td align="center"><strong>{#newsletter_subscription_active#}</strong></td>
        {/if}
        
    </tr>
    {foreach from=$addresses item=addr}
    <tr class="{cycle values="even,odd"}">
        <td><input type="checkbox" class="button" name="addresses[]" value="{$addr->id}"/>&nbsp;</td>
        {assign var='subscriptionsComa' value=$addr->subscriptions|@join:","}
        <td>
            {if $alladdresses==true}
            <a href="#" onclick="showEditAll('{$addr->id}','{$addr->address}','{$addr->language}','{$subscriptionsComa}');return false;"><img src="buttons/edit.gif" alt="{#edit#}" title="{#edit#}" /></a>
            {else}
            <a href="#" onclick="showEdit('{$addr->id}','{$addr->address}','{$addr->language}','{$addr->active}');return false;"><img src="buttons/edit.gif" alt="{#edit#}" title="{#edit#}" /></a>
            {/if}
        
        </td>
        <td>

            {if $alladdresses==true}
            <a href="#" onclick="showEditAll('{$addr->id}','{$addr->address}','{$addr->language}','{$subscriptionsComa}');return false;">{$addr->address}</a>
            {else}
            <a href="#" onclick="showEdit('{$addr->id}','{$addr->address}','{$addr->language}','{$addr->active}');return false;">{$addr->address}</a>
            {/if}        
        
        
        </td>
        <td align="center">{$addr->language}</td>
        {if $alladdresses==true}
        <td>
        {assign var=subsCount value=$addr->subscriptions|@count}
        {if $subsCount == 1}
            {getfield node=$addr->subscriptions[0] field=title var=nodetitle}
            - {$nodetitle}
        {elseif $subsCount > 1}
            {foreach from=$addr->subscriptions item=subs}
                {getfield node=$subs field=title var=nodetitle}
                - {$nodetitle}<br/>
            {/foreach}
        {/if}
    
        </td>
        {else}
        <td align="right">{$addr->subscription_date}</td>
        <td align="center">{$addr->active}</td>
        {/if}
    </tr>
    {/foreach}
    </table>
    
    <div style="padding: 10px 0;">
     {if $totalAddressCount > $addressesPerPage}
        {assign var='pages' value=$totalAddressCount/$addressesPerPage|ceil}
        {section name=pages start=1 loop=$pages+1}
            <a href="{url action0=$lastaction}&page={$smarty.section.pages.index}&showLangOnly={$smarty.request.showLangOnly}&contains={$smarty.request.contains}">
                {if $currentPageNr == $smarty.section.pages.index}<b style="color:orange;">{/if}
                {$smarty.section.pages.index}
                {if $currentPageNr == $smarty.section.pages.index}</b>{/if}</a> <span class="light">|</span>
        {/section}
    {/if}
    </div>
    
    <p class="spiner" style="float: right;">{$totalAddressCount} {#newsletter_addresses#}</p>
    <p class="action">{#newsletter_forselection#}:
    <input type="submit" name="{$delAddressAction}" value="{#newsletter_del_addresses#}" class="button" /></p>
    
    {if $alladdresses==false}
    <p class="action">
    {action action="newsletter:listaddresses:$newsletterRootNode:$lg"}<br/>
    <input type="submit" name="{$action}" value="{#s_done#}" class="button" />
    {/action}
    {/if}
    </p>
    </form>
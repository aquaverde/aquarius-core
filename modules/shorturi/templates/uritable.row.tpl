<tr id="uri_row_{$myindex}">
    <td width="100%" style="vertical-align:middle" nowrap="nowrap">
        <input type="hidden" name="uritableid[{$myindex}]" value="{$uri.id}" />
        <input type="hidden" id="delete_{$myindex}" name="delete[{$myindex}]" value="" />
        {#from_domain#}
        <input type="text" class="ef" style="margin:0px 6px 0 3px; width:18%;" name="from[{$myindex}]" value="{if $uri.domain}{$uri.domain}{/if}" />
        {#keyword#}
        <input type="text" class="ef" style="margin:0 3px; width:10%;" name="keyword[{$myindex}]" value="{if $uri.keyword}{$uri.keyword}{/if}" />
    
        {#to_url#}
        <input type="text" class="ef" style="margin:0 3px; width:51%;" name="url[{$myindex}]" value="{if $uri.redirect}{$uri.redirect}{/if}" />
        
        <img src="buttons/delete.gif" alt="delete" style="margin:0; padding:0; cursor:pointer;" onclick="delete_uri_row({$myindex});" />
    </td>
</tr>
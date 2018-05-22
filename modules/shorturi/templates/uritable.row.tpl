<tr id="uri_row_{$myindex}">
    <td>
        <input type="hidden" name="uritableid[{$myindex}]" value="{$uri.id}" />
        <input type="hidden" id="delete_{$myindex}" name="delete[{$myindex}]" value="" />
        {#from_domain#}
        <input type="text" class="form-control" name="from[{$myindex}]" value="{if $uri.domain}{$uri.domain}{/if}" />
    </td>
    <td width="5"><br>/</td>
    <td>
        {#keyword#}
        <input type="text" class="form-control" name="keyword[{$myindex}]" value="{if $uri.keyword}{$uri.keyword}{/if}" />
    </td>
    <td width="5"><br>→ </td>
    <td>
        {#to_url#}
        <input type="text" class="form-control" name="url[{$myindex}]" value="{if $uri.redirect}{$uri.redirect}{/if}" />
    </td>
    <td>
        <a href="#" onclick="delete_uri_row({$myindex});"><span class="glyphicon glyphicon-trash pull-right"></span></a>
    </td>
</tr>

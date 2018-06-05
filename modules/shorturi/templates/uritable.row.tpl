<tr id="uri_row_{$myindex}">
    <td width="180">
        <input type="hidden" name="uritableid[{$myindex}]" value="{$uri.id}" />
        <input type="hidden" id="delete_{$myindex}" name="delete[{$myindex}]" value="" />
        <input type="text" class="form-control" name="from[{$myindex}]" value="{if $uri.domain}{$uri.domain}{/if}" placeholder="{#from_domain#}" />
    </td>
    <td width="5">/</td>
    <td width="200">
        <input type="text" class="form-control" name="keyword[{$myindex}]" value="{if $uri.keyword}{$uri.keyword}{/if}" placeholder="{#keyword#}" />
    </td>
    <td width="5">→ </td>
    <td>
        
        <input type="text" class="form-control" name="url[{$myindex}]" value="{if $uri.redirect}{$uri.redirect}{/if}" placeholder="{#to_url#}" />
    </td>
    <td width="1">
        <a href="#" onclick="delete_uri_row({$myindex});"><span class="glyphicon glyphicon-trash pull-right"></span></a>
    </td>
</tr>

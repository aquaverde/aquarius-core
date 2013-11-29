<ul>
{foreach from=$field.node_options key=node_id item=node_props}
    <li>
        <input type="checkbox" value="{$node_id}" name="{$field.formname}[]" id="{$field.formname}-{$node_id}"
    {if $node_props.selected}checked="checked"{/if}/>&nbsp;<label for="{$field.formname}-{$node_id}">{$node_props.title|escape}</label>
    </li>
{/foreach}
</ul>
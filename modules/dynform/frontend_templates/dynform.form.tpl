<form method="post" name="mailForm" action="" id="form" 
    data-required="{wording This field is required.}" 
    data-email="{wording Please enter a valid email address.}" 
    data-number="{wording Please enter a valid number.}" 
    data-error="{wording Your form contains 1 error, see details below.}" 
    data-errors="{wording Your form contains _n_ errors, see details below.}"
>

    {foreach from=$dynform.blocks item='block'}
        <fieldset>
            {if $block.title}<legend>{$block.title}</legend>{/if}
            
            {foreach from=$block.fields item='field'}
                {include file="dynform.`$field.type`.tpl"}
            {/foreach}
        </fieldset>
    {/foreach}
    
    <input type="text" name="email_validate" id="email_validate" class="email_validate hidden" value="" />
    
    <span class="required">* {wording This field is required.}</span>
    <div class="errorMessage"></div>
    
    <span class="submit">
        <input type="submit" class="submit" name="dynform_submit" value="{wording send}">
    </span>
        
</form>
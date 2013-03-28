<form method="post" name="mailForm" action="" id="form" onsubmit="return checkFormSubmit(this);">

    <div class="formBlock clear">
                
        {foreach from=$dynform.blocks item=block}
                {if $block.title}
                    <div class="blockTitle">{$block.title}</div>
                {/if}

            
            {foreach from=$block.fields item=field}
                <div class="blockRow clear">
                    <label for='{$field.labeltofieldid}' id='{$field.labeltofieldid}Label'>
                    {$field.title|escape}
                    {if $field.required}&nbsp;*{/if}
                    </label>
                    {include file=dynform.`$field.type`.tpl}
                </div>
            {/foreach}
            
        {/foreach}
        
        <div id="validateEmail">
            {* spambot trap-field, must be hidden by CSS *}
            <input type="text" name="email_validate" id="email_validate" class="email_validate" value="" />
        </div>
        
        <span class="pleaseFill clear">* {wording these_fields_must_be_filled_in}</span>
        
        <span class="errorInvisible" id="errorMessage">{wording please_fill_in_required_fields}</span>
        
        <div class="formSubmit clear">
            <button type="submit" name="dynform_submit" class="btn btn-primary">{wording send}</button>
        </div>
            
	</div>
    
</form>
{include file='header.tpl'}
{include file='path.tpl'}

{if $command == "add"}
	{include file="formblocks/df_fb_top_add.tpl"}
{else}
 	{include file="formblocks/df_fb_top_edit.tpl"}
{/if}
 
    <div class='contentedit contentedit_mle'> 
        <label>
            Selection node
            <input name='field[options]' value="{$field->options|escape}" />
        </label>
    </div>

{if $command == "add"}
 	{include file="formblocks/df_fb_bottom_add.tpl"}
{else}
	{include file="formblocks/df_fb_bottom_edit.tpl"}
{/if}
 
{include file='footer.tpl'}

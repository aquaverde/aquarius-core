{include file='header.tpl'}
{include file='path.tpl'}


{if $command == "add"}
	{include file="formblocks/df_fb_top_add.tpl"}
{else}
 	{include file="formblocks/df_fb_top_edit.tpl"}
{/if}
 
 	{include file="formblocks/df_fb_num_lines.tpl"}
	
{if $command == "add"}
 	{include file="formblocks/df_fb_bottom_add.tpl"}
{else}
	{include file="formblocks/df_fb_bottom_edit.tpl"}
{/if}
 
{include file='footer.tpl'}
{include file='header.tpl'}
{include file='path.tpl'}

{if $command == "add"}
	{include file="formblocks/df_fb_top_add.tpl"}
{else}
 	{include file="formblocks/df_fb_top_edit.tpl"}
{/if}
 
 	{include file="formblocks/df_fb_additional_text.tpl"}
	{*include file="formblocks/df_fb_width.tpl"*}
	{include file="formblocks/df_fb_required.tpl"}
	
{if $command == "add"}
 	{include file="formblocks/df_fb_bottom_add.tpl"}
{else}
	{include file="formblocks/df_fb_bottom_edit.tpl"}
{/if}
 
{include file='footer.tpl'}

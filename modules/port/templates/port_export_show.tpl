{include file='header.tpl'}

<h1>{#port_dialog_title_export#}</h1>

<div class="bigbox">
    <textarea style="width: 100%; height: 50ex">{$export}</textarea>
    <form action="{url}" method="post">
        <input type="hidden" name="export_selected" value="{$smarty.request.export_selected}">
        <input type="hidden" name="include_children" value="{$smarty.request.include_children}">
    {include file='select_buttons.tpl'}
    </form>
</div>
{include file='footer.tpl'}

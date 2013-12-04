{include_javascript file='date-functions.js' lib=true}
{include_javascript file='datechooser.js' lib=true}
{include_javascript file='contentedit.date.js'}

<link rel='stylesheet' href='css/datechooser.css' type='text/css' />

<!--[if lte IE 6.5]>
    <link rel='stylesheet' href='css/archiver/select-free.css' type='text/css' />
<![endif]-->

<script type="text/javascript">
    Date.monthNames = {$smarty.config.month_names|split:","|@json}
    Date.dayNames = {$smarty.config.day_names|split:","|@json}
    
    {if $field.formfield->multi}
        var ac_index_{$field.htmlid} = 0;
    {/if}
</script>

<div id="my_dates_{$field.htmlid}">
    {foreach from=$field.value item='fileval' name='date_loop'}
        {include file='formfield.date.row.tpl'}
    
        {if $field.formfield->multi}
            <script type="text/javascript" charset="utf-8">
                ac_index_{$field.htmlid} = {$fileval.myindex};
            </script>
        {/if}
    {/foreach}
</div>

{if $field.formfield->multi}
    <img src="buttons/add.gif" alt="" style="float:left;margin-left:2px;cursor:pointer;" onclick="add_date_ajax('{$field.formfield->id}', '{$field.htmlid}');" />
{/if}
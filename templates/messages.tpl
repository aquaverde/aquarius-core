{foreach from=$messages item=message}
<div class="alert alert-success {$message->type()} alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <span class="glyphicon glyphicon-ok"></span>
  {$message->html()}
</div>
{/foreach}

{if $messagestrs|@count>0}
<div class="alert alert-success alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <span class="glyphicon glyphicon-ok pull-left"></span>
    <ul class="pull-left">
        {foreach from=$messagestrs item=message}
            <li>{$message|escape}</li>
        {/foreach}
    </ul>
    <div class="clear"></div>
</div>
{/if}
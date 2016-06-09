{foreach from=$messages item=message}
<div class="alert alert-{$message->type()|msgtype} alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <span class="glyphicon glyphicon-{$message->type()|msgglyph}"></span>
  {$message->html()}
</div>
{/foreach}

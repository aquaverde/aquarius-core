{assign var=format value=$smarty.const.DATE_FORMAT|replace:'%':''}

<div style="margin-bottom:5px;float:left;margin-right:5px;">
    <input class="inputsmall" id="{$field.htmlid}_{$fileval.myindex}" name="{$field.formname}" size="10" maxlength="10" type="text" value="{$fileval.date|escape}" />

    <img src="picts/date.gif" onclick="showChooser(this, '{$field.htmlid}_{$fileval.myindex}', 'chooserSpan{$field.htmlid}_{$fileval.myindex}', 1970, 2050, '{$format}', false);"/>

    <div id="chooserSpan{$field.htmlid}_{$fileval.myindex}" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px; margin-top:-105px; margin-left: 2px;">
    </div>
</div>
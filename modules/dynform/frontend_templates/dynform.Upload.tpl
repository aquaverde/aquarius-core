{if $field.options}
    <span class="formText">{$field.options|escape}</span>
{/if}
<div class="formInput file">
	<a class="delete"></a>
	<a class="clickCatcher"></a>
	<input id="datei" type="text" name="datei" class=" {$field.classstr}">
    <a class="browse button" href="#"><span>{wording Durchsuchen...}</span></a>
    <input type="file" name="{$field.id}" id="{$field.id}" class="visuallyhidden" value="" />
</div>
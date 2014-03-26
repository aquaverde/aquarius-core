<div class='contentedit contentedit_checkbox'> 
	<label for="required" title="">
        <input type="hidden" name="field[required]" value="0" />
        <input type="checkbox" name="field[required]" value="1" id="required" {if $field->required}checked="checked"{/if} />&nbsp;&nbsp;{#required#}
    </label>
	<label for="required" class="inline" title="required"></label>
<br/>
</div>
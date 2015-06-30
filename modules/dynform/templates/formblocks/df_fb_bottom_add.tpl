		
		<div class="clear"></div>
		<br />
		<input type="hidden" value="{$field_type}" name="field[type]" />
		
		{action action="dynform:addfieldsubmit:`$content->id`:`$content->lg`:`$node->id`:`$block->id`:`$field->id`"}
			<input type="submit" name="{$action}" value="{#s_save#}" class="btn btn-primary" />
		{/action}
		
		&nbsp;<input type="submit" name="" value="{#s_cancel#}" class="btn" />
	</div>
 </form>
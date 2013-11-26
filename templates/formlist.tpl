{include file='header.tpl'}
<h1>Pagetypes admin</h1>
<div class="">
    <div class="bigboxtitle"><h2>Edit or create a pagetype:</h2></div>
    <form action="{url action=$lastaction}" method="post">
        <table width="100%" cellpadding="0"  cellspacing="0" class="table table-hover">
{foreach from=$forms item=form}
	        <tr>
		        <td width="30%">
				  {action action="formedit:edit:`$form->id`"}
					<a href="{url action=$action action1=$lastaction}" title="edit pagetype">
						<img src="picts/form.gif" class="imagebutton" alt="form" align="absmiddle"/>
						{$form->title}
					</a>
				  {/action}
				</td>
		        <td>
                    <div class="dim">{if $form->template}{$form->template}{/if}</div>
				</td>				
				<td>
				  {count_form_used form_id=$form->id}
				</td>
                <td width="20">
				  {action action="formedit:edit:`$form->id`"}
					<a href="{url action=$action action1=$lastaction}">
						<img class="imagebutton" src="buttons/edit.gif" alt="edit" title="edit"/>
					</a>
				  {/action}
				</td>
                <td width="20">
				  {confirm yes="form:delete:`$form->id`" no=$lastaction title="Delete" message="Really delete?"}
					<input name="{$action}" class="imagebutton" type="image" src="buttons/delete.gif" title="delete" alt="delete"/>
				  {/confirm}
				</td>
                <td width="20">
				  {action action="form:copy:`$form->id`"}
					<input name="{$action}" class="imagebutton" type="image" title="copy" src="buttons/move.gif" alt="copy"/>
				  {/action}
				</td>
				<td width="20">
				  {action action="form:export:`$form->id`"}
					<input name="{$action}" class="imagebutton" type="image" title="save" src="buttons/export.gif" alt="save"/>
				  {/action}
				</td>
	        </tr>
{/foreach}		
        </table>
    </form>
    
    <a href="{url action1=$lastaction action='form:import'}" class="btn btn-default btn-sm pull-right"><span class="glyphicon glyphicon-download-alt"></span> Import</a>

    
    <form action="{url action=$lastaction}" method="post">
        <button type="submit" name="{$action}" class="btn btn-success btn-sm"><span class="glyphicon glyphicon-plus-sign"></span> {#s_new#}</button>
    </form>

    
    
    
    
</div>
{include file='footer.tpl'}
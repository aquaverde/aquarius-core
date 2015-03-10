{include file='header.tpl'}
<h1>Pagetypes admin</h1>
<div class="">
    <form action="{url action=$lastaction}" method="post">
        <table class="table table-hover">
            <tr>
                <th>Name</th>
                <th>Template</th>
                <th>Used</th>
                <th></th>
            </tr>
{foreach from=$formcts item=detail}
            {assign 'form' $detail.form}
	        <tr>
		        <td width="30%">
				  {action action="formedit:edit:`$form->id`"}
					<a href="{url action=$action action1=$lastaction}" title="edit pagetype">
						<span class="glyphicon glyphicon-list-alt"></span>  &nbsp; {$form->title}
					</a>
				  {/action}
				</td>
		        <td>
                    <div class="dim">{if $form->template}{$form->template}{/if}</div>
				</td>				
				<td>
                    {if $detail.count.node}
                        <a href="{url action1=$detail.example.node action2=$lastaction}">{$detail.count.node}</a>
                    {else}
                        ―
                    {/if}
                    {if $detail.count.nodechild}
                        <a href="{url action1=$detail.example.nodechild action2=$lastaction}">{$detail.count.nodechild}</a>
                    {else}
                        ―
                    {/if}
                    {if $detail.count.formchild}
                        <a href="{url action1=$detail.example.formchild action2=$lastaction}">{$detail.count.formchild}</a>
                    {else}
                        ―
                    {/if}
				</td>
                <td>
                    <div class="dropdown pull-right">
                        <a id="dLabel" role="button" data-toggle="dropdown" data-target="#" href="#" title="{#s_node_dropdown#}...">
                            <span class="glyphicon glyphicon-cog"></span><span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                            <li>
                                {confirm yes="form:delete:`$form->id`" no=$lastaction title="Delete" message="Really delete?"}
					               <button name="{$action}" class="btn btn-sm btn-link"><span class="glyphicon glyphicon-trash"></span> Delete...</button>
				                {/confirm}    
                            </li>
                            <li>
                                {action action="form:copy:`$form->id`"}
					               <button name="{$action}" class="btn btn-sm btn-link"><span class="glyphicon glyphicon-retweet"></span> Clone</button>
				                {/action}
                            </li>
                            <li>
                                {action action="form:export:`$form->id`"}
                                   <button name="{$action}" class="btn btn-sm btn-link"><span class="glyphicon glyphicon-export"></span> Export</button>
                                {/action}
                            </li>
                        </ul>
                    </div>

                </td>
	        </tr>
{/foreach}		
        </table>
    </form>
    <br>
        
    <a href="{url action1=$lastaction action='form:import'}" class="btn btn-default btn-sm pull-right"><span class="glyphicon glyphicon-download-alt"></span> Import</a>

    
    <form action="{url action=$lastaction}" method="post">
        <button type="submit" name="{$action_new}" class="btn btn-success btn-sm"><span class="glyphicon glyphicon-plus-sign"></span> {#s_new#}</button>
    </form>

    
    
    
    
</div>
{include file='footer.tpl'}
<div id="toolbar">
	<form name="toolbox" method="post" action="{url action0=$lastaction}"  enctype="multipart/form-data" accept-charset="UTF-8">

    	<div class="separator iconbar">

            <a href="#" onclick="document.getElementById('savebutton').click();return false;" data-toggle="tooltip" title="{#s_save#}"><span class="glyphicon glyphicon-floppy-disk"></span></a>&nbsp;

            {if $preview_uri|default:false}
                <a target="_new" href="{$preview_uri}" data-toggle="tooltip" title="{#s_page_view#}"><span class="glyphicon glyphicon-eye-open"></span></a>
            {/if}

            {if $node->id}
                {actionlink action="node:delete_dialog:`$node->id`" show_title=false}
                {actionlink action="node:moveorcopy:`$node->id`" show_title=false}
                &nbsp;{activationbutton action="node:toggle_active:`$node->id`" active=$node->active show_noedit=false}
            {/if}
            
        </div>

        {action action="node:editprop:`$node->id`"}
    	<div class="separator formbar dim">
                <a href="{url action0=$lastaction action1=$action}" data-toggle="tooltip" title="Node: {if $node->name}'{$node->name}'{/if} ID {$node->id}"><span class="glyphicon glyphicon-wrench"></span>Node</a>
        </div>
        {/action}


        {assign var=formedit value="formedit:edit:`$form->id`"|makeaction}
        {if $formedit || $change_form}
    	<div class="separator dim">
        {/if}

        {if $formedit}
    	    <a href="{url action0=$lastaction action1=$formedit}" title="{#s_form#}: {$form->title}"  data-toggle="tooltip">{#s_form#}:</a>
        {/if}
        {if $change_form}
            {if !$is_super}{#choose_form#}:&nbsp;{/if}
            <select name="form_id" class="form-control input-sm">
                {strip}
                    <option value="null" class="dim">{#inherited#}</option>
                {foreach from=$forms item='select_form'}
                    <option value="{$select_form->id}"{if $select_form->id == $node->form_id} selected="selected"{/if}>
                        {$select_form->title}
                    </option>
                {/foreach}
                {/strip}
            </select>
            <input type="submit" name="{$change_form}" value="{#change_form_submit#}" class="btn btn-default btn-xs" />
        {/if}
        
        {if $formedit || $change_form}
        </div>
       {/if}
 
        {if $formedit && $form->template}
            <div class="separator dim"><a href="{url action0=$lastaction action1=$formedit}" title="Template: {$form->template}"  data-toggle="tooltip">Template</a></div>
        {/if}
        
        {if $content->lg != $primary_lang->lg}
    	<div class="separator">
        	<a href="{url url=$url->with_param("copy-primary") action=$lastaction }">{#content_copy_primary#}: "{$primary_lang->name}"</a>
        </div>
        {/if}
        
         

    </form>
</div>
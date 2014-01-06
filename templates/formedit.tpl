{include file='header.tpl'}

{js}{literal}
    jQuery(function() {
        $("input").labelify({ labelledClass: 'dim' })
    })
{/literal}{/js}

<style type="text/css" media="all">{literal}
html, body {height: 95%} body {max-width: none;}
.wrapper { border: 0; width: 100%; max-width:none;}
{/literal}</style>
<h1>Pagetype "{$form->title}"</h1>
<form action="{url action="formedit:save:`$form->id`"}" method="post" style="display: inline">
    
    <div id="outer">
        <h2>Settings</h2>
        <label>Title</label> <input type="text" name="formtitle" value="{$form->title}" class="form-control"/>
        <label>Template</label> <input type="text" name="formtemplate" value="{$form->template}" class="form-control"/>
        <label>Sort by field</label> <input type="text" name="formsortby" value="{$form->sort_by}" class="form-control"/>
        <div class="inline-item">
        <label for="formsortreverse" style="min-width:150px;float:left;">Reverse sorting</label>
        <input type="checkbox" style="height:20px;" name="formsortreverse" id="formsortreverse" value="1" {if $form->sort_reverse}checked="checked"{/if}/>
        </div>
        <div class="inline-item">
        <label for="formshowinmenu" style="min-width:150px;float:left;">Show in Menu</label>
        <input type="checkbox" style="height:20px;" name="formshowinmenu" id="formshowinmenu" value="1" {if $form->show_in_menu}checked="checked"{/if}/>
		</div>
        <div class="inline-item">
        <label for="formfallthrough" class="inline" style="min-width:150px;display:block;float:left;">Fall through</label>
        {html_options name=formfallthrough options=$fallthroughoptions selected=$form->fall_through}
        </div>
		<div class="inline-item">
        <label for="formfallthrough" class="inline" style="min-width:150px;display:block;float:left;">Grouping selection</label>
        {html_options name=fieldgroup_selection options=$fieldgroup_selections selected=$form->fieldgroup_selection_id}
        </div>
        <div class="inline-item">
        <label for="formpermissionlevel" style="min-width:150px;display:block;float:left;">Permission</label>
        <select name="permission_level">
            {html_options options=$permission_levels selected=$form->permission_level}
        </select>
        </div>
    </div>
        <br>
        <h2>Fields</h2>
        <table class="table table-condensed table-hover" width="100%" cellpadding="0" cellspacing="2">
        <tr>
            <th>Active</th>
            <th width="12%" style="min-width: 150px;">Name</th>
            <th width="22%" style="min-width: 150px;">Description</th>
            <th>Type</th>
            <th>Weight</th>
            <th>Multi</th>
            <th>All lang</th>
            <th>Title</th>
            <th>Permission</th>
            <th>sup1 <div>int</div></th>
            <th>sup2 <div>int</div></th>
            <th>sup3 <div>varchar</div></th>
            <th>sup4 <div>varchar</div></th>
        </tr>
{foreach from=$fields item=field}
        <tr>
          <td><input class="checkbox" type="checkbox" id="field_{$field->id}_active" name="field[{$field->id}][active]"  value="1" {if $field->id|is_numeric}checked="checked"{/if}/></td>
          <td><input type="text" class="form-control" name="field[{$field->id}][name]" value="{$field->name|escape}" onChange="var field_active = document.getElementById('field_{$field->id}_active'); field_active.checked = this.value.length > 0;"/></td>
          <td><input type="text" class="form-control" name="field[{$field->id}][description]" value="{$field->description|escape}" title="{if $field->description}{else}{formfield_title f=$field}&nbsp;{/if}"/></td>
          <td>
            <select name="field[{$field->id}][type]">
  {foreach from=$formtypes item=type}
              <option value="{$type->get_code()}"{if $field->type==$type->get_code()}selected="selected"{/if}>{$type->get_title()|str}</option>
  {/foreach}
            </select>
          </td>
          <td><input type="text" class="form-control" name="field[{$field->id}][weight]" value="{$field->weight}" size="4"/></td>
          <td><input class="checkbox" type="checkbox" name="field[{$field->id}][multi]"  value="1" {if $field->multi}checked="checked"{/if}/></td>
          <td><input class="checkbox" type="checkbox" name="field[{$field->id}][language_independent]"  value="1" {if $field->language_independent}checked="checked"{/if}/></td>
          <td><input class="checkbox" type="checkbox" name="field[{$field->id}][add_to_title]"  value="1" {if $field->add_to_title}checked="checked"{/if}/></td>
          <td>
            <select name="field[{$field->id}][permission_level]">
                {html_options options=$permission_levels selected=$field->permission_level}
            </select>
          </td>
        {foreach from="sup1,sup2,sup3,sup4"|split item=sup}
            <td><input type="text" class="form-control" name="field[{$field->id}][{$sup}]" value="{$field->$sup|escape}"/></td>
        {/foreach}
        </tr>
{/foreach}
  </table>
  <input type="submit" name="{$action}" value="{#s_done#}" class="btn btn-primary"/>
  <input type="submit" name="{$lastaction}" value="{#s_save#}" class="btn btn-default"/>
</form>
<form action="{url}" method="post" style="display: inline">
  &nbsp;
  <input type="submit" value="{#s_cancel#}" class="btn btn-default"/>
</form>
{include file='footer.tpl'}
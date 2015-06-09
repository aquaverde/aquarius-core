{include file='header.tpl'}

{include file='path.tpl'}

{include_javascript file='nodetree.js'}
{include_javascript file='nodes_select.js' lib=true}
{include_javascript file='contentedit.pointing.js' lib=true}

<h1>{$node->title}</h1>
    
<div id="outer">
    <form name="nodeedit" method="post" action="{url}">
        <label>
            {#s_title#}<br/>
            <input class="form-control" type="text" name="title" value="{$node->title|escape}" id="nodedit_title"/>
        </label>
        
        <label>
            {#s_name#}<br/>
            <input class="form-control" type="text" name="name" value="{$node->name|escape}" id="nodedit_name"/>
        </label>

    {if $parent_select_action}
        <label for='nodeedit_parent'>
            {#s_parent_node#}<br/>
            <input type="hidden" id="parent_id" name="parent_id" value="{$node->parent_id}"/>
            <button
                type='button'
                name='nodeedit_parent' id='nodeedit_parent'
                class='btn btn-default btn-xs pointing_selection'
                data-url='{$simpleurl->with_param($parent_select_action)}'
                data-selected_field='parent_id'
                data-target='parent'
            >
                {$parent_select_action->get_title()}
            </button>
        </label>
        <div class="dim" id="parent_select_title_box" style="margin-top: 3px;" >
            {#s_pointings_selected#}:
            <span id="parent_titles" class="dim">
                {if $parent}{$parent->title}{/if}
            </span>
        </div>
    {/if}

        <label>
            {#s_form#}<br/>
            <select name="form_id" class="select">
                {foreach from=$allForms key=k item=i}
                    {if $k==$node->form_id}
                        <option value="{$k}" selected="selected">{$i}</option>
                    {else}
                        <option value="{$k}">{$i}</option>
                    {/if}
                {/foreach}
            </select>
        </label>

        <label>
            {#s_category_form#}<br/>
            <select name="childform_id" class="select">
                {foreach from=$allForms key=k item=i}
                    {if $k==$node->childform_id}
                        <option value="{$k}" selected="selected">{$i}</option>
                    {else}
                        <option value="{$k}">{$i}</option>
                    {/if}
                {/foreach}
            </select>
        </label>

        <label>
            {#s_content_form#}<br/>
            <select name="contentform_id" class="select">
                {foreach from=$allForms key=k item=i}
                    {if $k==$node->contentform_id}
                        <option value="{$k}" selected="selected">{$i}</option>
                    {else}
                        <option value="{$k}">{$i}</option>
                    {/if}
                {/foreach}
            </select>
        </label>
        
        <label>
            {#s_boxdepth#}<br/>
            <input class="form-control short" type="text" name="box_depth" value="{$node->box_depth|escape}" id="nodedit_boxdepth"/>
        </label>

        <label>
            <input class="checkbox" type="checkbox" name="active"  value="1" {if $node->active}checked="checked"{/if} id="nodedit_active"/>
            {#s_active#}
        </label>

        <label>
            <input class="checkbox" type="checkbox" name="access_restricted"  value="1" {if $node->access_restricted}checked="checked"{/if} id="nodedit_access_restricted"/>
            {#s_access_restricted#}
        </label>

        <div>
            <label style="display: inline">
                {#s_sort_by#}
                <input class="form-control short" type="text" name="sort_by" value="{$node->sort_by|escape}" style="display: inline">
            </label>
            
            <label style="display: inline">
                <input class="checkbox" type="checkbox" name="sort_reverse"  value="1" {if $node->sort_reverse}checked="checked"{/if}>
                {#s_sort_reverse#}
            </label>
        <div>

        <label>
            {#s_last_change#}<br/>
            <input class="form-control short" type="text" name="last_change" value="{$node->last_change|date_format:"%d.%m.%Y"}" disabled="disabled" />
        </label>

        <br/>
        <input type="hidden" name="submit" value="submit"/>
        <input type="submit" name="{$submitaction}" value="{#s_save#}" class="btn btn-primary" />
        <input type="submit" name="" value="{#s_cancel#}" class="btn btn-default"/>

    </form>

</div>

{include file='footer.tpl'}

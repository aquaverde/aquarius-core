{include file='header.tpl'}
<h1>{$lastaction->get_title()}</h1>

<form action="{url action=$lastaction}" method="post">
<div class="bigbox">
    <div class="bigboxtitle"><h2>Remove installer files</h2></div>
    {actionlink return=false button=true action=$remove_old_action}
</div>
</form>

<br/>

<form action="{url action=$lastaction}" method="post">
<div class="bigbox">
    <div class="bigboxtitle"><h2>Remove by pattern (Perl regexp)</h2></div>
    <input type="text" class="form-control" name="pattern" value=""/>
    {actionlink return=false button=true action=$remove_action}
</div>
</form>

<br/>

<h1>Copy files</h1>
<form action="{url action=$lastaction}" method="post">
<div class="bigbox">
    <div class="bigboxtitle"><h2>{#dircopy_source#}</h2></div>
    <i>{$root_dir}</i><input type="text" class="form-control" name="src" value="{$smarty.request.src|escape}" />

    <br /><br />
    <div class="bigboxtitle"><h2>{#dircopy_dest#}</h2></div>
    <i>{$root_dir}</i><input type="text" class="form-control" name="dst" value="{$smarty.request.dst|escape}"/>
    {actionlink action=$dircopy_action}
</div>


<h1>Access mode override</h1>
<form action="{url action=$lastaction}" method="post">
<div class="bigbox">
    <div class="bigboxtitle"><h2>Reset file access mode</h2></div>
    <i>{$root_dir}</i><input type="text" class="form-control" name="path" value="{$smarty.request.path|escape}" />
    for all files and directories in this path. DANGEROUS!
    <br /><br />
    <div class="bigboxtitle"><h2>Desired mode</h2></div>
    <input type="text" class="form-control" name="mode" value="{$smarty.request.mode|default:"0700"|escape}"/>
    {actionlink action=$mode_override_action}
</div>
</form>

{include file='footer.tpl'}
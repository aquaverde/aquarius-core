{include file='header.tpl'}
<h1>Add/Edit language</h1>

<form action="{url}" method="post">
    <div id="outer">
        <label for="nlg">Language 2-char ISO Code</label>
        <input type="text" name="nlg" value="{$editlang->lg}" class="ef"/>
        
        <label for="name">Display name</label>
        <input type="text" name="name" value="{$editlang->name}" class="ef"/>		

        {action action="languageAdmin:save:`$editlang->lg`"}
                <input 	type="submit" name="{$action}" value="{#s_save#}" class="submit"/>
        {/action}
                <input type="submit" name="" value="{#s_cancel#}" class="cancel"/>
    </div>
</form>

{include file='footer.tpl'}
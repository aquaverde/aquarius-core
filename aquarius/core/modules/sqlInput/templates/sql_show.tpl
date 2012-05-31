{include file='header.tpl'}

<h1>{#sql_input#}</h1>
<p>{#einl_text#}</p><br/>

<form action="{url action=$lastaction}" enctype="multipart/form-data" method="post" accept-charset="utf-8">
    <input type="file" name="sql_file" id="sql_file" />
    <p><input type="submit" value="{#senden#}" class="submit"></p>
</form>

{include file='footer.tpl'}
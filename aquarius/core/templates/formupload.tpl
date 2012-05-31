{include file='header.tpl'}
<h1>Form-Definition XML Upload</h1>
<form action="{$url}" method="post" enctype="multipart/form-data">
    <label for="file">Filename:</label>
    <br />
    <input type="file" name="xmlfile" id="xmlfile" />
    <br />
    <br />
    {action action="form:import_form_submit"}
        <input type="submit" name="{$action}"  value="Submit" />
    {/action}
</form>

{include file='footer.tpl'}
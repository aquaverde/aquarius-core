{include file="header.tpl"}

<form action="{url action=$lastaction}" method="post">
    <h1>{$lastaction->get_title()}</h1>

    <div>
        <h2>Export</h2>
        {actionlink action="wording:export:0" button=true}
        {actionlink action="wording:export:1" button=true}
        <br><br>
        <h2>Import CSV key,lg1,lg2,...</h2>
        <form action="{$lastaction}" method="post">
        <textarea name="wording_csv"  class="mle" rows="5" cols="80"></textarea>

        {include file=select_buttons.tpl}
        </form>
    </div>
</form>
{include file="footer.tpl"}
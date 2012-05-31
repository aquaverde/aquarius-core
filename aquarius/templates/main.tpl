{extends skeleton.tpl}
{block name=body}
    <div id="wrapper">
        {include file=header.tpl}
        <div id="content" class="clear">
            {block name=content}{/block}
        </div>
        <div id="clear"></div>
    </div>
    {include file=footer.tpl}
{/block}
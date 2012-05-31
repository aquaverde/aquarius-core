<div id="header">
    <div id="logo">
        {link node=home}<img src="/interface/logo.png" alt="" />{/link}
        <ul class="language">
            {foreach_language node=$node}
                <li><a href="{href node=$entry.node lg=$entry.lg}" {if $entry.lg == $lg}class="on"{/if}>{$entry.lg}</a></li>
            {/foreach_language}
        </ul>
    </div>
    <div class="imgSlider">
        {getfield field=picture_header var=pictures shuffle=true inherit=true}
        {foreach from=$pictures item=picture}
                <div class="panel{if $smarty.foreach.iteration.first} current{/if}">
                <img src="{resize image=$picture.file width=1000 height=370}" alt="{$picture.legend}" />
            </div>
        {/foreach}
        </ul>
    </div>
	{include file=navig.tpl}
</div>
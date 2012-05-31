
{assign var="publicpath" value=$fileinfo.publicpath}

{if $fileinfo.type == "image"}
    <a href="javascript:openBrWindow('{url action0="filemgr:showPicture:`$fileinfo.publicpath`:" }','FileManager','width={$fileinfo.size.0+30},height={$fileinfo.size.1+30},top=100,left=200,toolbar=no,status=yes,resizable=yes,menubar=no')">
    <img src="{$publicpath|th}" alt="{#s_show_file#}" title="{#s_show_file#}" {$fileinfo.th_attrs}/>
    </a>
{elseif $fileinfo.type == "flash"}
    <object type="application/x-shockwave-flash" data="{$publicpath}" width="100" height="50">
        <param name="movie" value="{$publicpath}" />
    </object>
{else}
    <a href="{$publicpath}" target="_new">
    <img src="buttons/{$fileinfo.button}" alt="{$fileinfo.name}" title="{$fileinfo.name}"/></a>
{/if}
		<p class="spiner">{#s_spiner_pages#}: <span class="light">|</span>&nbsp;
		
		{if $spinner->hasPrev() }
			{assign var="prevPos" value=$spinner->prevPosition() }
			{action action=$spinner->position_action($prevPos)}
				<a href="{url action0=$action}">&laquo;</a> <span class="light">|</span>
			{/action}
		{/if}

		{section name=loop start=0 loop=$spinner->getPageCount()}
			{assign var="nextPos" value=$spinner->nextPosition($smarty.section.loop.index) }
				{action action=$spinner->position_action($nextPos)}
					<a href="{url action0=$action}">
				{/action}
				{if $spinner->isSelecetedPosition($smarty.section.loop.index) }
					<b style="color:orange;">
				{/if}
					{$smarty.section.loop.index+1}
				{if $spinner->isSelecetedPosition($smarty.section.loop.index) }
					</b>
				{/if}
				</a> <span class="light">|</span>
		{/section}
		
		{action action=$spinner->position_action("showAll")}
			<a href="{url action0=$action}">
		{/action}
		
		{if $spinner->showAll() }
			<b style="color:orange;">
		{/if}
				{#s_filter_reset#}
		{if $spinner->showAll() }
			</b>
		{/if}
		</a> <span class="light">|</span>
		
		{if $spinner->hasNext() }
			{assign var="nextPos" value=$spinner->nextPosition() }
			{action action=$spinner->position_action($nextPos)}
				<a href="{url action0=$action}">&raquo;</a>
			{/action}
		{/if}
		
		</p>
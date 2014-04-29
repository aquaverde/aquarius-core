<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.map.php
 * Type:     function
 * Name:     mapmenu
 * Purpose:  creates a google mapmenu  
 * -------------------------------------------------------------
 */
function smarty_function_mapmenu($params, &$smarty) {
	global $aquarius;
	$maps_modul = $aquarius->modules['maps'];
	$content_list = $maps_modul->get_content_list($smarty->get_template_vars('lg'));
	$categories = $maps_modul->get_categories($content_list);
	$overcats = $maps_modul->get_overcats($content_list);
	
	echo '<div id="map_menu"><a href="#" style="float:left;" onclick="show_all(); return false;">'.db_Wording::getTranslation('all', $smarty->get_template_vars('lg')).'</a>';
	foreach ($categories as $key => $value) {
		echo '
			<div class="map_menu_entry">
				<input type="checkbox" style="float:left;" id="checkbox_'.$value.'" value="'.$value.'" onchange="show_hide_cat(\''.$value.'\',this); return false;" ';
				if(PREVIEW_CAT == $value || PREVIEW_CAT == "all") echo 'checked="checked"';
		echo ' /><a href="#" onclick="show_cat_only(\''.$value.'\',\'cat\'); return false;">'.$value.'</a>
			</div>
		';
	}
	foreach ($overcats as $key => $value) {
		echo '
			<div class="map_menu_entry">
				<input style="float:left;" id="checkbox_'.$value.'" type="checkbox" value="'.$value.'" onchange="show_hide_overcat(\''.$value.'\',this); return false;" ';
				if(PREVIEW_CAT == $value || PREVIEW_CAT == "all") echo 'checked="checked"';
		echo ' /><div style="float:left;" onclick="open_close_overcat(\''.$value.'\'); return false;"><img id="'.$value.'_pic" src="/interface/arrow_map-off.gif" /></div><a href="#" onclick="show_cat_only(\''.$value.'\',\'overcat\'); return false;">'.$value.'</a>
				<div id="'.$value.'_uc" class="map_menu_entry_uc" value="closed">
				';
				foreach($maps_modul->get_cats_from_overcats($content_list,$value) as $uc) {
					echo '
						<div class="map_menu_entry">
							<input type="checkbox" style="float:left;" id="checkbox_'.$uc.'" value="'.$uc.'" onchange="show_hide_cat(\''.$uc.'\',this); return false;" ';
							if(PREVIEW_CAT == $value || PREVIEW_CAT == "all") echo 'checked="checked"';
					echo ' /><a href="#" style="float:left;" onclick="show_cat_only(\''.$uc.'\',\'cat\'); return false;">'.$uc.'</a>';
					echo '</div><br />';
				}
		echo '
				</div>
			</div>
		';
	}
	echo '</div>';
}

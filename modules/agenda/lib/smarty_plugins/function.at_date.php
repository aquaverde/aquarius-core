<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.at_date.php
 * Type:     function
 * Name:     at_date
 * Purpose:  gets nodes from the agenda on a specified day  
 * -------------------------------------------------------------
 */
function smarty_function_at_date($params, &$smarty)
{
	$agenda_modul = new Agenda();
	
	if(empty($params['date'])) exit("You have to give me a date: {at_date date=dd.MM.yyyy}");
	
	$smarty->assign("my_data", $agenda_modul->get_data_by_date($params['date']));
}
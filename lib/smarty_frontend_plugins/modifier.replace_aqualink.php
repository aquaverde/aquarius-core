<?php
/**
 * replace the 'aquarius-node (intern Aquarius)'-links written by an RTE in backend 
 * with an browser-readable link
 *
 * RTEinternLink example: <a href="aquarius-node:342">NodeName</a>
 * Replaced Link: <a href="http://www.yourdomain.com/de/Node/">NodeName</a>
 *
 *
 * Example: {$text|replace_aqualink}
 *
 * @param string $text 
 * @return (modified) text
 * @author Tobias Kneubühler
 */

function smarty_modifier_replace_aqualink($text) 
{	
	global $smarty;
	$suchmuster	= "/<[\s]*a[\s]+href=[\"']aquarius-node:([0-9]+)[\"'][\s]*>/";
	preg_match_all($suchmuster, $text, $mynodes);
	
	$nodelist = $mynodes[1];

	if(count($nodelist) > 0) {
		foreach ($nodelist as $node_id) {
			$url		= db_Node::get_node($node_id)->get_href($smarty->get_template_vars('lg'));
			$muster		= "/<[\s]*a[\s]+href=[\"']aquarius-node:".$node_id."[\"'][\s]*>/";
			$ersetzung 	= '<a href="'.$url.'">';
			$text 		= preg_replace($muster, $ersetzung, $text);
		}
	}
 		
	return $text;	
}
?>
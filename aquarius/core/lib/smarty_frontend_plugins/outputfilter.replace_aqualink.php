<?php
/**
 * replace the 'aquarius-node (intern Aquarius)'-links written by an RTE in backend 
 * with an browser-readable link
 *
 * RTEinternLink example: <a href="aquarius-node:342">NodeName</a>
 * Replaced Link: <a href="http://www.yourdomain.com/de/Node/">NodeName</a>
 *
 *
 *
 * @param string $text 
 * @return (modified) text
 * @author Tobias Kneubühler
 */

function smarty_outputfilter_replace_aqualink($text, $smarty) 
{	
	$suchmuster	= "/<[\s]*a[\s]+href=[\"']aquarius-node:([0-9]+)[\"'][\s]*>/";
	preg_match_all($suchmuster, $text, $mynodes);
	
	$nodelist = $mynodes[1];

	foreach ($nodelist as $node_id) {
		$node		= DB_Node::get_node($node_id);
		
		if(!$node) {
			Log::warn("Can`t find node '$node_id' for replacing in outputfiler 'replace_aqualink' for ".$smarty->get_template_vars('node')->idstr());
			continue;
		}
		
		$url		= $smarty->uri->to($node);
		$muster		= "/<[\s]*a[\s]+href=[\"']aquarius-node:".$node_id."[\"'][\s]*>/";
		$ersetzung 	= '<a href="'.$url.'">';
		$text 		= preg_replace($muster, $ersetzung, $text);
	}
 		
	return $text;	
}
?>
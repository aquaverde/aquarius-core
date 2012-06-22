<?php
class action_mailChimp extends ModuleAction {
	var $modname = "mailChimp";
	var $props = array('class', 'op', 'node_id', 'chimp', 'lg');
    
    function valid($user) {
      return (bool)$user;
    }
}

class action_mailChimp_upload extends action_mailChimp implements DisplayAction
{

	function process($aquarius,$request,$smarty,$result) 
	{
		

		$newsletters = db_Node::get_node('newsletters');
		$newsletters = $newsletters->children();

		$smarty->assign("newsletters", $newsletters);

		//DO THE CHIMP
		require_once 'MCAPI.class.php';
		require_once 'config.inc.php';

		$api = new MCAPI($apikey);

		$retval = $api->campaigns();

		if ($api->errorCode){
			echo "Unable to Pull list of Campaign!";
			echo "\n\tCode=".$api->errorCode;
			echo "\n\tMsg=".$api->errorMessage."\n";
		} else {
		    $smarty->assign("count_campaigns", sizeof($retval['data']));
		    $smarty->assign("campaigns", $retval['data']);
		}

		$result->use_template("upload.tpl");
	}

}

class action_mailChimp_upload_letter extends action_mailChimp implements DisplayAction
{

	function process($aquarius,$request,$smarty,$result) 
	{
		
		$chimp 					= (bool)$this->chimp;

		$newsletter 			= db_Node::get_node($this->node_id);
		$kats 					= $newsletter->children();

		$results = array();
		foreach($kats as $kat)
		{
			$results[$kat->title] 	= array();
			$entries 				= $kat->children();

			foreach($entries as $entry)
			{
				$results[$kat->title][] = $entry;
			}
		}

		$old_smarty = $smarty;
		$smarty 	= $aquarius->get_smarty_frontend_container($this->lg, $newsletter);

		$smarty->assign("newsletter", $newsletter);
		$smarty->assign("results", $results);

		$myhtml = $smarty->fetch("newsletter_mail.tpl");

		if($chimp)
		{
			//DO THE CHIMP
			require_once 'MCAPI.class.php';
			require_once 'config.inc.php';

			$api = new MCAPI($apikey);

			$type = 'regular';

			$opts['list_id'] 	= $listId;
			$opts['subject'] 	= $newsletter->title;
			$opts['from_email']	= $my_email; 
			$opts['from_name'] 	= $my_name;

			$opts['tracking']	= array('opens' => true, 'html_clicks' => true, 'text_clicks' => false);

			$opts['authenticate'] 	= true;
			//$opts['analytics'] 		= array('google'=>'my_google_analytics_key');
			$opts['title'] 			= $newsletter->title;

			$content = array(
						'html'=> $myhtml
					);

			$retval = $api->campaignCreate($type, $opts, $content);

			if ($api->errorCode)
			{

				$upload_result = "Es konnte leider keine neue Kampagne erstellt werden!";
				$upload_result .= "\n\tCode=".$api->errorCode;
				$upload_result .= "\n\tMsg=".$api->errorMessage."\n";

			} else 
			{
				$upload_result = "Neue Kampagne '$newsletter->title' erfolgreich auf MailChimp erstellt!";
			}

			$old_smarty->assign("upload_result", $upload_result);
			$result->use_template("upload_letter.tpl");
		}
		else
		{
			$old_smarty->assign("myhtml", $myhtml);
			$result->use_template("upload_letter.tpl");
		}
		
	}

}

?>
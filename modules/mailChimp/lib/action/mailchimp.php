<?php

require('MailChimp-api.php');
use \DrewM\MailChimp\MailChimp;

class action_mailChimp extends ModuleAction {
    var $modname = "mailChimp";
    
    function valid($user) {
      return (bool)$user;
    }
    
    function MCAPI() {
        return new MailChimp($this->module->conf('apikey'));
    }
}

class action_mailChimp_upload extends action_mailChimp implements DisplayAction {

    function process($aquarius,$request,$smarty,$result) {
        $newsletters = db_Node::get_node('newsletters');
        $newsletters = $newsletters->children();

        $smarty->assign("newsletters", $newsletters);

        $api = $this->MCAPI();

        $retval = $api->get("campaigns");

        if ($api->errorCode){
            echo "Unable to Pull list of Campaign!";
            echo "\n\tCode=".$api->errorCode;
            echo "\n\tMsg=".$api->errorMessage."\n";
        } else {
            $campaigns = $retval['campaigns'];
            $smarty->assign("count_campaigns", count($campaigns));
            $smarty->assign("campaigns", $campaigns);
        }

        $result->use_template("upload.tpl");
    }

}


class action_mailChimp_select_lg extends action_mailChimp implements DisplayAction {

    var $props = array('class', 'op', 'node_id');
    
    function process($aquarius, $request, $smarty, $result) {
        $newsletter = db_Node::get_node($this->node_id);
        if (!$newsletter) throw new Exception("Newsletter not found");
        $smarty->assign("newsletter", $newsletter);
        
        $langsel = array();
        foreach(db_Languages::getLanguages() as $lang) {
            $langcontent = $newsletter->get_content($lang->lg);
            if ($langcontent) {
                $langsel[$lang->lg] = $langcontent;
            }
        }
        $smarty->assign("langsel", $langsel);
        $smarty->assign("nextaction", Action::make('mailchimp', 'select_list', $this->node_id, false));
        
        $result->use_template("mailchimp_select_lg.tpl");
    }
}

class action_mailChimp_select_list extends action_mailChimp implements DisplayAction {

    var $props = array('class', 'op', 'node_id', 'lg');
    
	function process($aquarius, $request, $smarty, $result) {
        $newsletter = db_Node::get_node($this->node_id);
        if (!$newsletter) throw new Exception("Newsletter not found");
        
        $this->lg = get($request, 'newsletter_lg', $this->lg);
        
        if (strlen($this->lg) < 1) {
            // Game over, try again
            return;
        }
        
        $smarty->assign("newsletter", $newsletter);
        $smarty->assign("newsletter_lg", $this->lg);

		$api = $this->MCAPI();
		$listsw = $api->lists();

		if ($api->errorCode) throw new Exception("Failed fetching list of mailing lists: Code= ($api->errorCode), Msg=$api->errorMessage.");

        $smarty->assign("lists", $listsw['data']);
        $smarty->assign("nextaction", Action::make('mailchimp', 'create_campaign', $this->node_id, $this->lg, false));
        
		$result->use_template("mailchimp_select_list.tpl");
	}
}




class action_mailChimp_create_campaign extends action_mailChimp implements ChangeAction {

    var $props = array('class', 'op', 'node_id', 'lg', 'list_id');
    
    function get_title() { return "Kampagne erstellen"; }
    
    function process($aquarius, $post, $result) {
        
        $newsletter = db_Node::get_node($this->node_id);
        if (!$newsletter) throw new Exception("Newsletter not found");

        $smarty     = $aquarius->get_smarty_frontend_container($this->lg, $newsletter);
        $smarty->caching = false;

        $this->list_id = get($post, 'list_id', $this->list_id);
        
        $smarty->assign("newsletter", $newsletter);


        $type = 'regular';
        
        $opts['list_id']    = $this->list_id;
        $opts['subject']    = $smarty->get_template_vars('title');
        $opts['from_email'] = $this->module->conf('email'); 
        $opts['from_name']  = $this->module->conf('name');

        $opts['tracking']   = array('opens' => true, 'html_clicks' => true, 'text_clicks' => false);

        $opts['authenticate']   = true;
        //$opts['analytics']        = array('google'=>'my_google_analytics_key');
        $opts['title']          = $smarty->get_template_vars('title')." ($this->lg)";

        $myhtml = $smarty->fetch("newsletter_mail.tpl");
        $content = array(
                    'html'=> $myhtml
                );

        $api =  $this->MCAPI();
        $retval = $api->campaignCreate($type, $opts, $content);

        if ($api->errorCode) {
            $result->add_message(AdminMessage::with_html('warn', "Es konnte leider keine neue Kampagne erstellt werden! Code=$api->errorCode Msg=$api->errorMessage"));
        } else {
            $result->add_message("Neue Kampagne '$newsletter->title' erfolgreich auf MailChimp erstellt!");
        }
    }

}

class action_mailChimp_preview extends action_mailChimp implements SideAction {
    var $props = array('class', 'op', 'lg', 'node_id');

    function get_title() { return "Vorschau"; }
    
	function process($aquarius,$request) {
		$newsletter = db_Node::get_node($this->node_id);
		if (!$newsletter) throw new Exception("Newsletter not found");

		$smarty = $aquarius->get_smarty_frontend_container($this->lg, $newsletter);
        $smarty->caching = false;
		$smarty->assign("newsletter", $newsletter);
		$smarty->display("newsletter_mail.tpl");
	}

}

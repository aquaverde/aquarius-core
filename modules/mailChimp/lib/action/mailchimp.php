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

        if ($api->success()){
            $campaigns = $retval['campaigns'];
            $smarty->assign("count_campaigns", count($campaigns));
            $smarty->assign("campaigns", $campaigns);
        } else {
            $smarty->assign("apierror", "Unable to Pull list of Campaign: ".$api->getLastError());
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
        $listsw = $api->get("lists");

	if (!$api->success()) {
            throw new Exception("Failed fetching list of mailing lists: ".$api->getLastError());
        }

        $smarty->assign("lists", $listsw['lists']);
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
        $html_content = $smarty->fetch("newsletter_mail.tpl");

        $api = $this->MCAPI();
        $res = $api->post("campaigns", 
            [ 'type' => 'regular'
            , 'recipients' => [ 'list_id' => $this->list_id ]
            , 'settings' =>
                [ 'subject_line' => $smarty->get_template_vars('title')
                , 'title' => $smarty->get_template_vars('title')." ($this->lg)"
                , 'from_name' => $this->module->conf('name')
                , 'reply_to' => $this->module->conf('email')
                , 'authenticate' => true
                ]
            , 'tracking' =>
                [ 'opens' => true
                , 'html_clicks' => true
                , 'text_clicks' => false
                ]
            ]
        );

        if (!$api->success()) {
            $result->add_message(AdminMessage::with_html('warn', "Erstellen der Kampagne fehlgeschlagen: ".$api->getLastError()));
            return;
	}

        $campaign_id = $res['id'];

        $api->put("/campaigns/$campaign_id/content", [ 'html' => $html_content ]);

        if (!$api->success()) {
            $result->add_message(AdminMessage::with_html('warn', "Übertragen des HTML-Inhalts fehlgeschlagen: ".$api->getLastError()));
            return;
	}

        $result->add_message("Neue Kampagne '$newsletter->title' erfolgreich auf MailChimp erstellt!");
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

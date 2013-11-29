<?php 
require_once('Mail.php');
require_once('Mail/mime.php');
require_once('Mail/RFC822.php');

/** A newsletter edition in a certain language that can be previewed or sent out. */
class Newsletter_Edition {
    /** Create a newsletter edition for the given node
      * @param $edition_node Create instance for this newsletter edition
      * @param $lg the language of the content
      * @param $smarty the smarty container to use when building the mail body
      */
    function __construct($edition_node, $lg, $smarty) {
        $this->node = $edition_node;
        $this->newsletter_node = $edition_node->get_parent();
        $this->newsletter_content = $this->newsletter_node->get_content($lg);
        if (!$this->newsletter_content) throw new Exception("Failed loading content for newsletter node $this->newsletter_node language $lg");
        $this->newsletter_content->load_fields();

        $this->edition_content = $edition_node->get_content($lg);
        if (!$this->edition_content) throw new Exception("Failed loading content for edition node $this->edition_node language $lg");
        $this->edition_content->load_fields();

        $this->lg = $lg;
        $this->smarty = $smarty;
    }

    /** Build header lines */
    function header() {
        $header = array();
        $header['To']       = '"'.$this->newsletter_content->from.'" <>' ;
        $header['From']     = '"'.$this->newsletter_content->from.'" <'.$this->newsletter_content->from_email.'>';
        $header['Reply-To'] = '"'.$this->newsletter_content->from.'" <'.$this->newsletter_content->from_email.'>';
        $header['Subject']  = $this->edition_content->title();

        return $header;
    }

    /** Build edition body with template specified in newsletter
      * Reads the template to use from the field 'template' in the newsletter content. */
    function body() {
        $this->prepare_template();
        return $this->smarty->fetch($this->template_file());
    }
    
    /** What template file to use */
    function template_file() {
        return $this->newsletter_content->template.".tpl";
    }
    
    /** Assign newsletter specific variables to the container */
    function prepare_template() {
        // Load edition parts
        $fields = array();
        foreach ($this->node->children(array('inactive_self')) as $edition_field_node) {
            $content = $edition_field_node->get_content($this->lg);
            if ($content && $content->active) {
                $content->load_fields();
                $fields[] = $content;
            }
        }

        $smarty = $this->smarty;
        $smarty->assign('lg', $this->lg);
    
        $smarty->assign('newsletter_content', $this->newsletter_content);
        $smarty->assign('edition', $this->edition_content);
        $smarty->assign('edition_content', $this->edition_content); // Yeah, this is assigned twice. I don't know which one the templates use
        $smarty->assign('fields', $fields);

        $newsletter_root = $this->newsletter_node->get_parent();
        $smarty->assign('newsletter_root_node', $newsletter_root);

        $unsubscribe_uri = $smarty->uri->to($newsletter_root);
        $unsubscribe_uri->add_params(array(
                'unsubscribe' => 1,
                'nl'          => $this->newsletter_node->id
        ));
        $smarty->assign('unsubscribe_link', $unsubscribe_uri);
    }
}
?>
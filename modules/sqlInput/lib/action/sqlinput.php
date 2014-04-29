<?php
class action_sqlInput extends ModuleAction {
	var $modname = "sqlInput";
	var $props = array('class', 'op', 'lg');
}

class action_sqlInput_showInput extends action_sqlInput implements DisplayAction {
	
	function process($aquarius,$request,$smarty,$result) {        
		global $DB;

        // FIXME: The database part must be done in a ChangeAction
		if(isset($_FILES['sql_file'])) {
		    $file = $_FILES['sql_file']['tmp_name'];
		    if(file_exists($file)) {
		        //$query = file_get_contents($file);
                $lines = file($file);
                
                if(count($lines) >= 10) {
                    $DB->query("truncate table projekte");
                    $DB->query("truncate table tagliste");
                    
                    foreach ($lines as $line) {
                        $DB->query($line);
                    }	            

                    // $handle = fopen ($file, "r");
                    // $contents = fread ($handle, filesize($file));
                    // fclose ($handle);
                    // print_r($contents);
                    // $DB->query($contents);

    	            unlink($file);

                    $message = new AdminMessage('ok');
                    $message->add_line('sqlFile_succes');
                    $result->add_message($message);
                }
                else {
                    throw new Exception("there are not enough lines in file '".$file."'");
                }
                
		    } else {
		        throw new Exception("could not find file '".$file."'");
		    }
		}

		$module = $this->get_module();
		$result->use_template("sql_show.tpl");
	}
}

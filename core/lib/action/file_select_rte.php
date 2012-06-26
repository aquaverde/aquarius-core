<?
/**
 * special file_select action for using in an RTE
 * we have to assign the CKEditorFuncNum variable (sended from the RTE) to call the right function
 * from RTE to fill in the selected file 
 */
Action::use_class('file_select');
class action_file_select_rte extends action_file_select implements DisplayAction {
    
    function process($aquarius, $request, $smarty, $result) {
        $this->target_id = get($request, 'CKEditorFuncNum', $this->target_id);        
        
        parent::process($aquarius, $request, $smarty, $result);                        
    }
}
?>
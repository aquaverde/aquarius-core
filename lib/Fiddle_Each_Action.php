<?php
/** Transitory action to modify the actions in the action stack */
class Fiddle_Each_Action implements FiddlingAction {
    function __construct($fiddler) {
        $this->fiddler = $fiddler;
    } 
   
    function permit() {
        return true;
    }
   
    function process($aquarius, &$actions) {
        $fiddler = $this->fiddler;
        foreach($actions as $action) {
            $fiddler($action);
        }
    }

    function __toString() {
        return "Fiddling action";
    }
}
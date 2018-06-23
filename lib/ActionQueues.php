<?php

class ActionQueues {
    var $_changes = [];
    var $_sides = [];
    var $_rest = [];

    function __construct($actions) {
        // Remove duplicates from actions
        $pending = array();
        foreach($actions as $action) {
            $keep = true;
            foreach($pending as $pendingaction) {
                if ($action->equals($pendingaction)) {
                    $keep = false;
                    break;
                }
            }
            if ($keep) {
                $pending []= $action;
                Log::debug("Pushing ".$action->actionstr());
            }
        }

        // Sort them by their sequence
        usort($pending, function($a, $b) { return $b->sequence - $a->sequence; });

        // Sort them into action groups
        foreach($pending as $action) {
            if      ($action instanceof ChangeAction) $this->_changes  []= $action;
            else if ($action instanceof SideAction)   $this->_sides    []= $action;
            else                                      $this->_rest     []= $action;
        }
    }

    function inject($action) {
        array_unshift($this->_rest, $action);
    }

    /** List of change actions*/
    function changes() {
        return $this->_changes;
    }

    /** List of side actions */
    function sides() {
        return $this->_sides;
    }

    /** List of display actions */
    function displays() {
        // For legacy reasons there are weird things still processed together with display actions
        // See rest(). Here we filter for DisplayAction instances to avoid the worst of CSRF when
        // passing actions through the login form.
        return array_filter($this->_rest, function($action) {
            return $action instanceof DisplayAction;
        });
    }

    /** List of DisplayActions and other legacy actions */
    function rest() {
        return $this->_rest;
    }
}
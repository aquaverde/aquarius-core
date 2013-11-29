<?php 

class spinner {
	
	public $position;
	public $max_per_page;
	public $elements_count;
	public $baseAction;

    /** @param $position current item index
      * @param $max_per_page how many items per page
      * @param $elements_count total amount of items
      * @param $baseAction base action for what ought to happen if a spinner link is clicked
      * @param $set_position_callback function that modifies base action to point to a certain position. If this parameter is omitted, $baseAction must be an action string. */
	function __construct($position, $max_per_page, $elements_count, $baseAction, $set_position_callback = false) {
		$this->position = $position;
        if (empty($this->position)) $this->position = 0;
		$this->baseAction = $baseAction;
		$this->max_per_page	= $max_per_page;
		$this->elements_count = $elements_count;
        $this->set_position_callback = $set_position_callback;
	}

    /** Return the currently active part of a list.
      * @param $list A (numerically indexed?) array
      * @return The part of $list that is shown for this spinner */
    function current_slice($list) {
        if ($this->showAll()) return $list;
        else return array_slice($list, $this->position, $this->max_per_page);
    }

	function getPageCount() {
		return ceil($this->elements_count / $this->max_per_page);
	}
	
	function hasNext() {
		return ( ($this->position + $this->max_per_page) <= $this->elements_count );
	}

	function hasPrev() {
		return ( $this->position != "0" && $this->position != "showAll" );
	}
	
	function prevPosition($actualPosition = '') {
		if ( $actualPosition != '' )
			return ($actualPosition-1) * $this->max_per_page;
		
		return $this->position - $this->max_per_page;
	}
	
	function nextPosition($actualPosition = '') {
		if ( $actualPosition == "0" )
				return 0;
		if ( $actualPosition != '' )
			return $actualPosition * $this->max_per_page;
		
		return $this->position + $this->max_per_page;
	}
	
	function isSelecetedPosition($position) {
		return ( ($position * $this->max_per_page) == $this->position 
				&& !$this->showAll() );
	}
	
	/*
		function returns true if 'showAll' was seleceted by
		a user. else it returns false
	*/
	function showAll() {
		return ( is_String($this->position) && $this->position == "showAll" );
	}
	
	function show($inPosition) {
		if ( $this->showAll() ) return true;
		
		if ( $this->elements_count > $this->max_per_page )
			if ( $inPosition < $this->position || $inPosition >= ($this->position+$this->max_per_page ) )
				return false;
		return true;
	}

    /** Get modified base action
      * @param $position to which position the action should go
      * @return the base action modified by the set_position_callback. If the callback is not set, $baseaction is assumed to hold an action string and the position is appended.*/
    function position_action($position) {
        $callback = $this->set_position_callback;
        if ($callback) {
            return $callback(clone $this->baseAction, $position);
        } else {
            return $this->baseAction.$position;
        }
    }
}


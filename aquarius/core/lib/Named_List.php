<?
/** Manage an ordered list of named items
  * This is in many ways the same as a PHP array when used as dictionary. Its strength is adding items anywhere in the list next to known names. */
class Named_List implements IteratorAggregate {
    private $items = array();
    private $ordering = array();

    /** Add an item to the list
      *   @param $name name of the item
      *   @param $item the item to be added (passing null removes the item)
      *   @param $location where to insert, either 'before' or 'after' (optional, preset is 'after')
      *   @param $relative_to insert before or after this name (optional)
      *   @return integer index where the item was inserted (before deletion). Null if item was not inserted.
      * If an item already exists under $name, it will be replaced by the new item. If parameter $relative_to is not in the list, the item is not added.
      */
    function add($name, $item, $location='after', $relative_to=null) {
        $insert_position = null;
        $before = $location == 'before';
        if ($relative_to) {
            foreach($this->ordering as $position=>$position_name) {
                if ($position_name == $relative_to) {
                    $insert_position = $position + ($before ? 0 : 1);
                }

                // Mark for removal all entries with same name
                if ($position_name == $name) {
                    $this->ordering[$position] = false;
                }
            }
        } else {
            $insert_position = $before ? 0 : count($this->ordering);
        }

        if ($insert_position !== null) {
            array_splice($this->ordering, $insert_position, 0, array($name));

            $this->ordering = array_filter($this->ordering);

            $this->items[$name] = $item;
        }
    }

    /** Get the item associated with given name */
    function get($name) {
        return get($this->items, $name, null);
    }

    /** Return ordered list of all items, with their name as key */
    function items() {
        $items = array();
        foreach($this->ordering as $name) {
            $items[$name] = $this->items[$name];
        }
        return $items;
    }

    function getIterator() {
        return new ArrayIterator($this->items());
    }
}

?>
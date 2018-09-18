<?php 
/** @package Aquarius
  * Comparator classes for your sorting needs
  * Trash classes like these would not be necessary if PHP had closures. (Just sayin)
  */

/** Compare objects */
class ObjectCompare {
    /** Create a comparator callback for a field
      * @param $field name of the property to compare
      * @param $comp optional comparison function, or callback. Defaults to 'strcasecmp'
      * @return a callback, ready to be used.
      */
    static function by_field($field, $comp='strcasecmp') {
        $c = new self();
        $c->field = $field;
        $c->comp = $comp;
        return array($c, 'by_field_cmp');
    }

    function by_field_cmp($o1, $o2) {
        assert(is_object($o1));
        assert(is_object($o2));
        return call_user_func($this->comp, $o1->{$this->field}, $o2->{$this->field});
    }
}

/** Compare arrays
  * Example usage:
  *
  *   $fruits = array(
  *      'apples'  => array('colour'=>'green',  'shape'=>'round',      'price'=>15),
  *      'pears'   => array('colour'=>'green',  'shape'=>'like pears', 'price'=>10),
  *      'bananas' => array('colour'=>'yellow', 'shape'=>'bent',       'price'=>30),
  *   )
  *   usort($fruits, ArrayCompare::by_entry('price', 'intcmp'));
  *   // Now $fruits are sorted by price
  *
  * Note that we had to specify 'intcmp' as comparison function because the default is a string comparison.
  */
class ArrayCompare {
    /** Create a comparator callback for a dict entry
      * @param $key name of the entry to compare
      * @param $comp optional comparison function, or callback. Defaults to 'strcasecmp'
      * @return a callback, ready to be used.
      */
    static function by_entry($key, $comp='strcasecmp') {
        $c = new self();
        $c->key = $key;
        $c->comp = $comp;
        return array($c, 'by_entry_cmp');
    }

    function by_entry_cmp($a1, $a2) {
        assert(is_array($a1));
        assert(isset($a1[$this->key]));
        assert(is_array($a2));
        assert(isset($a2[$this->key]));
        return call_user_func($this->comp, $a1[$this->key], $a2[$this->key]);
    }
}

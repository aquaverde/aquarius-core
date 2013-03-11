<?php 
/** @package Aquarius
  */

/** Defer execution of a function until its value is needed and cache the returned value for the future.
  */
class LazyCache {
    /** Wrap a method call
      * @param $object target object
      * @param $method target method
      * @return LazyCache object wrapping the method call */
    static function object_call($object, $method) {
        return new LazyCache(array($object, $method));
    }

    /** Build a lazycache wrapper for given function call
      * @param $function_callback function call to execute when value is requested. This function is called at most once.
      */
    function __construct($function_callback) {
        if (!is_callable($function_callback)) throw new Exception('Passed function callback is not callable');
        $this->function_callback = $function_callback;
    }

    /** Get return value of function
      * The first time this method is called, the function is called and its value returned. On subsequent calls, the cached value is returned.*/
    function get() {
        if ($this->function_callback) {
            $this->cached_result = call_user_func($this->function_callback);
            $this->function_callback = null;
        }
        return $this->cached_result;
    }
}

?>
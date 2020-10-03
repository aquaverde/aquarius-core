<?php

require_once dirname(__FILE__)."/../lib/Named_List.php";

class Named_List_Test extends PHPUnit_Framework_TestCase {
    function setUp() {
        $this->list = new Named_List();
    }

    function testEmpty() {
        $this->assertEquals(array(), $this->list->items());
    }

    function testOne() {
        $this->list->add('one', 1);
        $this->assertEquals(array('one' => 1), $this->list->items());
    }

    function testTwo() {
        $this->list->add('one', 1);
        $this->list->add('two', 2);
        $this->assertEquals(array('one' => 1, 'two' => 2), $this->list->items());
    }

    function testBefore() {
        $this->list->add('two', 2);
        $this->list->add('three', 3);
        $this->list->add('one', 1, 'before');
        $this->assertEquals(array('one' => 1, 'two' => 2, 'three' => 3), $this->list->items());
    }

    function testAfter() {
        $this->list->add('one', 1);
        $this->list->add('two', 2);
        $this->list->add('three', 3, 'after');
        $this->assertEquals(array('one' => 1, 'two' => 2, 'three' => 3), $this->list->items());
    }

    function testNamedBefore() {
        $this->list->add('one', 1);
        $this->list->add('three', 3);
        $this->list->add('two', 2, 'before', 'three');
        $this->assertEquals(array('one' => 1, 'two' => 2, 'three' => 3), $this->list->items());
    }

    function testNamedAfter() {
        $this->list->add('one', 1);
        $this->list->add('three', 3);
        $this->list->add('two', 2, 'after', 'one');
        $this->assertEquals(array('one' => 1, 'two' => 2, 'three' => 3), $this->list->items());
    }

    function testReplace() {
        $this->list->add('one', 1);
        $this->list->add('one', 111);
        $this->assertEquals(array('one' => 111), $this->list->items());
    }

    function testMiddleReplace() {
        $this->list->add('one', 1);
        $this->list->add('two', 2);
        $this->list->add('three', 3);
        $this->list->add('two', 222);
        $this->list->add('one', 111);
        $this->list->add('three', 333);
        $this->list->add('twoandahalf', 25, 'before', 'three');
        $items = $this->list->items();
        print_r($this->list);
        foreach(array(111, 222, 25, 333) as $value) {
            $this->assertEquals($value, array_shift($items));
        }
        $this->assertTrue(count($items) == 0);
    }
}
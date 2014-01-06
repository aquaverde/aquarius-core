<?php

require_once dirname(__FILE__)."/../lib/FilterParser.php";

class FilterParserTest_PredicateTrue {
    function pass($_) {
        return true;
    }
}

class FilterParserTest_PredicateAnd {
    function __construct($clauses) {
        $this->clauses = $clauses;
    }
    function pass($thing) {
        foreach($this->clauses as $clause) {
            if (!$clause->pass($thing)) return false;
        }
        return true;
    }
}

class FilterParserTest_PredicateOr {
    function __construct($clauses) {
        $this->clauses = $clauses;
    }
    function pass($thing) {
        foreach($this->clauses as $clause) {
            if ($clause->pass($thing)) return true;
        }
        return false;
    }
}

class FilterParserTest_PredicateNot {
    function __construct($clause) {
        $this->clause = $clause;
    }
    function pass($thing) {
        return !$this->clause->pass($thing);
    }
}

class FilterParserTest extends PHPUnit_Framework_TestCase {
    function setUp() {
        $this->parser = new FilterParser();
        $this->parser->add_predicates(array(
            'true' => create_function('$parser', 'return new FilterParserTest_PredicateTrue();'),
            'and'  => create_function('$parser', '$clauses = array($parser->consume_statement(), $parser->parse()); return new FilterParserTest_PredicateAnd($clauses);'),
            'or'  => create_function('$parser', '$clauses = array($parser->consume_statement(), $parser->parse()); return new FilterParserTest_PredicateOr($clauses);'),
            'not'  => create_function('$parser', '$clause = $parser->parse_predicate(); return new FilterParserTest_PredicateNot($clause);'),
        ));
    }
    function testEmpty() {
        $this->assertEquals(false, $this->parser->interpret(''));
    }
    function loadFilter($filter_sentence) {
        return $this->parser->interpret($filter_sentence);
    }
    function testTrue() {
        $this->assertTrue($this->loadFilter('true')->pass('whatever'));
    }
    function testNot() {
        $this->assertFalse($this->loadFilter('not true')->pass('whatever'));
    }
    function testAnd() {
        $this->assertTrue($this->loadFilter('true and true')->pass('whatever'));
    }
    function testAndNot() {
        $this->assertFalse($this->loadFilter('true and not true')->pass('whatever'));
    }
    function testOr() {
        $this->assertTrue($this->loadFilter('not true or true')->pass('whatever'));
    }
    function testOrNot() {
        $this->assertFalse($this->loadFilter('not true or not true')->pass('whatever'));
    }
    function testWhitespace() {
        $this->assertTrue($this->loadFilter(
            "\t\r\ntrue
            and\rtrue\nand\ttrue
                                                                                                                            ")->pass('whatever'));
    }
    
    function testBigLong() {
        $long_sentence = "true";
        for($n=0; $n<100; $n++) {
            $long_sentence .= ' and true';
            $this->assertTrue($this->loadFilter($long_sentence)->pass('whatever'));
        }
    }

    function testInvalidOrPrefixed() {
        try {
            $this->parser->interpret('or true');
            $this->fail('Expected FilterParsingException');
        } catch (FilterParsingException $expected) {}
    }
    function testInvalidAndPostfixed() {
        try {
            $this->parser->interpret('true and true and');
            $this->fail('Expected FilterParsingException');
        } catch (FilterParsingException $expected) {}
    }
    function testInvalidLoneNot() {
        try {
            $this->parser->interpret('not');
            $this->fail('Expected FilterParsingException');
        } catch (FilterParsingException $expected) {}
    }
    function testInvalidOrphanArgument() {
        try {
            $this->parser->interpret('true oprhan');
            $this->fail('Expected FilterParsingException');
        } catch (FilterParsingException $expected) {}
    }
    function testInvalidOprhanArgumentBeforeOperator() {
        try {
            $this->parser->interpret('true orphan and true');
            $this->fail('Expected FilterParsingException');
        } catch (FilterParsingException $expected) {}
    }
    function testInvalidPredicate() {
        try {
            $this->parser->interpret('EICAROsi0quee');
            $this->fail('Expected FilterParsingException');
        } catch (FilterParsingException $expected) {}
    }
    function testInvalidPredicateAfterOperator() {
        try {
            $this->parser->interpret('true and EICAROsi3queee');
            $this->fail('Expected FilterParsingException');
        } catch (FilterParsingException $expected) {}
    }
}

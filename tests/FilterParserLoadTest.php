<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__ . "/../lib/FilterParser.php";
require_once __DIR__ . "/../lib//NodeFilter.php";

class FilterParserLoadTest_PredicateTrue implements Filter {
    function pass($_) {
        return true;
    }
}

class FilterParserLoadTest extends TestCase {
    protected function setUp(): void {
        $this->parser = new FilterParser(__DIR__ . "/../lib/predicates/");
        $this->parser->add_predicates(array(
            'true' => function($parser) {
                return new FilterParserLoadTest_PredicateTrue(); }
        ));
    }

    function testNotPredicateCanBeLoaded() {
        $this->assertFalse(
            $this->parser->interpret('not true')->pass('anything'));
    }

    function testOrPredicateCanBeLoaded() {
        $this->assertTrue(
            $this->parser->interpret('true or true')->pass('anything'));
    }

    function testHasPredicateCanBeLoaded() {
        $this->assertInstanceOf(
            Filter::class,
            $this->parser->interpret('has field value'));
    }

    function testaccessiblePredicateCanBeLoaded() {
        $this->assertInstanceOf(
            Filter::class,
            $this->parser->interpret('accessible for user'));
    }

    function testPredicateCantBeLoaded() {
        $this->expectException(FilterParsingException::class);

        $this->parser->interpret('EICAROsi3queee');
        $this->fail('Expected FilterParsingException');
    }
}

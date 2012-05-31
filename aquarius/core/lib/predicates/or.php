/** logical or predicate
  * Synopsis: <left-predicate> or <right-predicate> */
$clauses = array($parser->consume_statement(), $parser->parse());
return new Filter_Logic_Or($clauses);

<?php

namespace App\DQL;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;

class UnixTimestamp extends FunctionNode
{
    protected $arithmeticExprt;

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'UNIX_TIMESTAMP ('.
            $sqlWalker->walkArithmeticExpression($this->arithmeticExprt)
        .')';
    }

   /**
    * parse - allows DQL to breakdown the DQL string into a processable structure
    * @param \Doctrine\ORM\Query\Parser $parser
    */
    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->arithmeticExprt = $parser->ArithmeticExpression(); //Specify the value of the function
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}

<?php
/**
 * {license_notice}
 *
 * @copyright {copyright}
 * @license   {license_link}
 */
namespace Magento\Tools\Formatter\PrettyPrinter\Statement;

use PHPParser_Node_Expr_LogicalXor;

class LogicalXorOperator extends InfixOperatorAbstract
{
    public function __construct(PHPParser_Node_Expr_LogicalXor $node)
    {
        parent::__construct($node);
    }
    public function operator()
    {
        return ' xor ';
    }
    /* 'Expr_LogicalXor'       => array(17, -1), */
    public function associativity()
    {
        return -1;
    }
    public function precedence()
    {
        return 17;
    }
}

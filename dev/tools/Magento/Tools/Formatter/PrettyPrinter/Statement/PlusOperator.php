<?php
/**
 * {license_notice}
 *
 * @copyright {copyright}
 * @license   {license_link}
 */
namespace Magento\Tools\Formatter\PrettyPrinter\Statement;

use PHPParser_Node_Expr_Plus;

class PlusOperator extends InfixOperatorAbstract
{
    public function __construct(PHPParser_Node_Expr_Plus $node)
    {
        parent::__construct($node);
    }
    public function operator()
    {
        return ' + ';
    }
    /* 'Expr_Plus'             => array( 5, -1), */
    public function associativity()
    {
        return -1;
    }
    public function precedence()
    {
        return 5;
    }
}

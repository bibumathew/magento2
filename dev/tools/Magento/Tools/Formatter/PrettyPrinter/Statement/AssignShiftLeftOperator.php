<?php
/**
 * {license_notice}
 *
 * @copyright {copyright}
 * @license   {license_link}
 */
namespace Magento\Tools\Formatter\PrettyPrinter\Statement;

use PHPParser_Node_Expr_AssignShiftLeft;

class AssignShiftLeftOperator extends AssignmentOperatorAbstract
{
    public function __construct(PHPParser_Node_Expr_AssignShiftLeft $node)
    {
        parent::__construct($node);
    }
    public function operator()
    {
        return ' <<= ';
    }
    /* 'Expr_AssignShiftLeft'  => array(15,  1), */
    public function associativity()
    {
        return 1;
    }
    public function precedence()
    {
        return 15;
    }
}

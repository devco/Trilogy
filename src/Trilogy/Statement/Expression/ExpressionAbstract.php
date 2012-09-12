<?php

namespace Trilogy\Statement\Expression;

abstract class ExpressionAbstract implements ExpressionInterface
{
    public function __construct($expr = null)
    {
        if ($expr) {
            $this->parse($expr);
        }
    }
}
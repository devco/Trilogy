<?php

namespace Trilogy\Statement\Expression;

interface ExpressionInterface
{
    public function __construct($expr = null);
    
    public function parse($expr);
}
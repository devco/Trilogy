<?php

namespace Trilogy\Statement;

/**
 * Represents a DELETE statement.
 * 
 * @category Iterators
 * @package  Trilogy
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  MIT http://opensource.org/licenses/mit-license.php
 */
class Remove extends StatementAbstract
{
    use Type\Source,
        Type\Where;
}
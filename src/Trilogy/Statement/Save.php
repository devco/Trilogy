<?php

namespace Trilogy\Statement;

/**
 * Represents a INSERT / UPDATE statement.
 * 
 * @category Iterators
 * @package  Trilogy
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  MIT http://opensource.org/licenses/mit-license.php
 */
class Save extends StatementAbstract
{
    use Type\Data,
        Type\Source,
        Type\Where;
}
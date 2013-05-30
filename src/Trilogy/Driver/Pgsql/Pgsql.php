<?php

namespace Trilogy\Driver\Pgsql;
use Trilogy\Driver\Sql\SqlAbstract;
use Trilogy\Statement;

/**
 * Handles PosgreSQL specifics.
 * 
 * @category Iterators
 * @package  Trilogy
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  MIT http://opensource.org/licenses/mit-license.php
 */
class Pgsql extends SqlAbstract
{
    /**
     * All DBs handle limiting differently.
     * 
     * @param int $limit  The limit.
     * @param int $offset The offset.
     * 
     * @return string
     */
    public function compileLimit(Statement\StatementInterface $stmt)
    {
        if (!$stmt->getLimit()) {
            return;
        }
        return 'LIMIT ? OFFSET ?';
    }

    /**
     * Converts the value to the driver specific Boolean value
     *
     * @param $value The value to be converted
     *
     * @return mixed
     */
    public function filterBool($value)
    {
        if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        }

        return $value;
    }
}
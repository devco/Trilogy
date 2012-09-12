<?php

namespace Trilogy\Driver;
use Trilogy\Statement\StatementInterface;

/**
 * Handles MySQL specifics.
 * 
 * @category Iterators
 * @package  Trilogy
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  MIT http://opensource.org/licenses/mit-license.php
 */
class Mysql extends SqlDriverAbstract
{
    /**
     * All DBs handle limiting differently.
     * 
     * @param int $limit  The limit.
     * @param int $offset The offset.
     * 
     * @return string
     */
    protected function compileLimit(StatementInterface $stmt)
    {
        $limit = $stmt->getLimit();
        
        if (!$limit) {
            return;
        }
        
        $sql = 'LIMIT ' . $limit;
        
        if ($offset = $stmt->getOffset()) {
            $sql .= ', ' . $offset;
        }
        
        return $sql;
    }
}
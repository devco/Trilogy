<?php

namespace Trilogy;

/**
 * Class autoloader.
 * 
 * @category Iterators
 * @package  Trilogy
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  MIT http://opensource.org/licenses/mit-license.php
 */
class Autoloader
{
    /**
     * Registers autoloading via SPL.
     * 
     * @return void
     */
    public static function register()
    {
        spl_autoload_register(function($class) {
            require_once __DIR__
                . DIRECTORY_SEPARATOR
                . '..'
                . DIRECTORY_SEPARATOR
                . str_replace('\\', DIRECTORY_SEPARATOR, $class)
                . '.php';
        });
    }
}
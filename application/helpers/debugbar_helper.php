<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Debugbar Helper Functions
 * Similar to Laravel Debugbar helper functions
 */

if (!function_exists('debugbar')) {
    /**
     * Get the Debugbar instance
     * 
     * @return object Debugbar instance
     */
    function debugbar()
    {
        $CI =& get_instance();
        if (!isset($CI->debugbar)) {
            $CI->load->library('debugbar');
        }
        return $CI->debugbar;
    }
}

if (!function_exists('debug')) {
    /**
     * Add a debug message to the debugbar
     * 
     * @param mixed $message The message to log
     * @param string $type The type of message (info, warning, error)
     */
    function debug($message, $type = 'info')
    {
        if (ENVIRONMENT === 'development') {
            $debugbar = debugbar();
            if ($debugbar && $debugbar->isEnabled()) {
                if (is_array($message) || is_object($message)) {
                    $message = print_r($message, TRUE);
                }
                $debugbar->addMessage($message, $type);
            }
        }
    }
}

if (!function_exists('debug_info')) {
    /**
     * Add an info message to the debugbar
     * 
     * @param mixed $message The message to log
     */
    function debug_info($message)
    {
        debug($message, 'info');
    }
}

if (!function_exists('debug_warning')) {
    /**
     * Add a warning message to the debugbar
     * 
     * @param mixed $message The message to log
     */
    function debug_warning($message)
    {
        debug($message, 'warning');
    }
}

if (!function_exists('debug_error')) {
    /**
     * Add an error message to the debugbar
     * 
     * @param mixed $message The message to log
     */
    function debug_error($message)
    {
        debug($message, 'error');
    }
}






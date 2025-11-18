<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Debugbar Hook
 * Initializes and renders the Debugbar library
 */
class Debugbar_hook
{
    /**
     * Initialize debugbar after controller is loaded
     */
    public function init_debugbar()
    {
        // Only enable in development environment
        if (ENVIRONMENT === 'development') {
            $CI =& get_instance();
            $CI->load->library('debugbar');
        }
    }
    
    /**
     * Render debugbar after controller execution
     */
    public function render_debugbar()
    {
        // Only enable in development environment
        if (ENVIRONMENT === 'development') {
            $CI =& get_instance();
            
            // Make sure debugbar is loaded
            if (!isset($CI->debugbar)) {
                $CI->load->library('debugbar');
            }
            
            // Render the debugbar
            if (isset($CI->debugbar) && $CI->debugbar->isEnabled()) {
                $CI->debugbar->render();
            }
        }
    }
}


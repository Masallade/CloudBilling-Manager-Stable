<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter Debugbar Library
 * Similar to Laravel Debugbar for CodeIgniter
 * 
 * @package     CodeIgniter
 * @subpackage  Libraries
 * @category    Debugging
 */
class Debugbar
{
    private $CI;
    private $enabled = FALSE;
    private $data = array();
    private $queries = array();
    private $start_time;
    private $start_memory;
    private $messages = array();
    private $views = array();
    private $files = array();
    
    public function __construct()
    {
        $this->CI =& get_instance();
        $this->start_time = microtime(TRUE);
        $this->start_memory = memory_get_usage();
        
        // Enable only in development environment
        if (ENVIRONMENT === 'development') {
            $this->enabled = TRUE;
            $this->initialize();
        }
    }
    
    /**
     * Initialize the debugbar
     */
    private function initialize()
    {
        // Hook into database queries
        $this->CI->db->save_queries = TRUE;
    }
    
    /**
     * Add a message to the debugbar
     */
    public function addMessage($message, $type = 'info')
    {
        if (!$this->enabled) return;
        
        $this->messages[] = array(
            'message' => $message,
            'type' => $type,
            'time' => microtime(TRUE) - $this->start_time
        );
    }
    
    /**
     * Add a view file to the debugbar
     */
    public function addView($view)
    {
        if (!$this->enabled) return;
        
        $this->views[] = array(
            'view' => $view,
            'time' => microtime(TRUE) - $this->start_time
        );
    }
    
    /**
     * Collect database queries
     */
    public function collectQueries()
    {
        if (!$this->enabled) return;
        
        $queries = $this->CI->db->queries;
        $query_times = isset($this->CI->db->query_times) ? $this->CI->db->query_times : array();
        
        foreach ($queries as $key => $query) {
            $this->queries[] = array(
                'query' => $query,
                'time' => isset($query_times[$key]) ? $query_times[$key] : 0,
                'bindings' => array()
            );
        }
    }
    
    /**
     * Get all collected data
     */
    private function getData()
    {
        $end_time = microtime(TRUE);
        $end_memory = memory_get_usage();
        
        $this->collectQueries();
        
        return array(
            'execution_time' => round(($end_time - $this->start_time) * 1000, 2),
            'memory_usage' => $this->formatBytes($end_memory - $this->start_memory),
            'memory_peak' => $this->formatBytes(memory_get_peak_usage()),
            'queries' => $this->queries,
            'queries_count' => count($this->queries),
            'messages' => $this->messages,
            'views' => $this->views,
            'server' => array(
                'method' => $this->CI->input->server('REQUEST_METHOD'),
                'uri' => $this->CI->uri->uri_string(),
                'controller' => $this->CI->router->class,
                'method' => $this->CI->router->method,
                'ip' => $this->CI->input->ip_address(),
                'user_agent' => $this->CI->input->user_agent()
            ),
            'get' => $_GET,
            'post' => $_POST,
            'session' => isset($_SESSION) ? $_SESSION : array(),
            'files' => $this->getIncludedFiles()
        );
    }
    
    /**
     * Get included files
     */
    private function getIncludedFiles()
    {
        $files = get_included_files();
        $file_list = array();
        
        foreach ($files as $file) {
            $file_list[] = array(
                'file' => str_replace(BASEPATH, 'system/', $file),
                'size' => filesize($file)
            );
        }
        
        return $file_list;
    }
    
    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    /**
     * Render the debugbar
     */
    public function render()
    {
        if (!$this->enabled) return;
        
        // Don't render for AJAX requests or if output already sent
        if ($this->CI->input->is_ajax_request() || headers_sent()) {
            return;
        }
        
        $data = $this->getData();
        
        // Get the output
        $output = $this->CI->output->get_output();
        
        // Inject debugbar before closing body tag
        $debugbar_html = $this->CI->load->view('debugbar/debugbar', $data, TRUE);
        
        // Try to inject before </body>
        if (strpos($output, '</body>') !== FALSE) {
            $output = str_replace('</body>', $debugbar_html . '</body>', $output);
        } else {
            // If no body tag, append at the end
            $output .= $debugbar_html;
        }
        
        $this->CI->output->set_output($output);
    }
    
    /**
     * Enable/Disable debugbar
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }
    
    /**
     * Check if debugbar is enabled
     */
    public function isEnabled()
    {
        return $this->enabled;
    }
}



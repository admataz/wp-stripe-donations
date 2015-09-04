<?php

namespace adz_stripe_donations;
use \Exception as Exception;

class Base {
    protected $module_slug = '';
    protected $plugin_slug = '';
    protected static $instance = null;
    /**
     * return identifiers
     */
    public function get_slug($function = '') {
        $slug = $this->plugin_slug;
        if(strlen($this->module_slug)){
         $slug .= '_' . $this->module_slug;
        }
        if (strlen($function)) {
            $slug.= '_' . $function;
        }
        return $slug;
    }
    
    public function __construct($plugin_slug) {
        $this->plugin_slug = $plugin_slug;
    }
    /**
     * singleton pattern  - makes sense for most of these
     */
    public static function get_instance($plugin_slug = 'AdzPlugin') {
        // http://stackoverflow.com/a/3126181
        static $instances = array();
        $calledClass = get_called_class();
        $id = $calledClass . '_' . $plugin_slug;
        if (!isset($instances[$id])) {
            $instances[$id] = new $calledClass($plugin_slug);
        }
        return $instances[$id];
    }
    /**
     * abstract the storage retrieval mechanism for the host application - defaulting to WordPress because that's what we're writing for now
     */
    protected function get_option($id = 'settings', $default = '') {
        if (empty($id)) {
            $id = $this->get_slug();
            // throw new Exception('No id provided for option/setting storage retrieval');
        }

        return get_option($id, $default);
    }


    public function get($item_id = null, $settings_id = null, $default='') {
      $options = $this->get_option($settings_id);
      if(!$item_id){
        return $options;
      }

      if(!isset($options[$item_id])){
        return $default;
      }

      return $options[$item_id];

    }


    /**
     * abstract the storage update mechanism
     */
    protected function set_option($id = '', $value = '') {
        if (empty($id)) {
            $id = $this->get_slug();
            // throw new Exception('No id provided for option/setting storage update');
        }
        update_option($id, $value);
    }
    
    function _convert_utf8_hack($str) {
        return preg_replace('/\\\u([0-9a-z]{4})/', '&#x$1;', ($str));
    }
    
    function object_to_array($obj) {
        if (is_object($obj)) $obj = (array)$obj;
        if (is_array($obj)) {
            $new = array();
            
            foreach ($obj as $key => $val) {
                $new[$key] = $this->object_to_array($val);
            }
        } 
        else $new = $obj;
        return $new;
    }
}

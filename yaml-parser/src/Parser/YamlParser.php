<?php
namespace Parser;
use Exception;

class YamlParser {
    protected $obj;
    public function __construct($filename) {
        try {
            $this->obj = Spicy::loadFile($filename);
        } catch(Exception $e) {
            throw new Exception("yaml file not found!");
        }
    }

    public function getImage($name) {
        if(isset($this->obj[$name])) {
            return $this->obj[$name];
        } else {
            throw new Exception("Require Image Name!");
        }
    }

    public function getLinks($name) {
        return (isset($this->obj[$name])) ? $this->obj[$name] : null;
    }

    public function getStep($name) {
        return (isset($this->obj[$name])) ? $this->obj[$name] : null;
    }

    public function getNotify($name) {
        return (isset($this->obj[$name])) ? $this->obj[$name] : null;
    }
}

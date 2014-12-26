<?php
class YamlParser {
	protected $obj;
	public function __construct($filename) {
		$this->obj = Spicy::loadFile($filename);
	}

	public function getImage($name) {
		if(isset($this->obj[$name])) {
			return $this->obj[$name];
		} else {
			throw new Exception("Require Image Name!");
		}

	}

	public function getStep($name) {
		return (isset($this->obj[$name])) ? $this->obj[$name] : null;
	}

	public function getNotify($name) {
		return (isset($this->obj[$name])) ? $this->obj[$name] : null;
	}
}

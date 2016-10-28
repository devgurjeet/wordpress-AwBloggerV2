<?php

class AwbConfigReader {
	private $url;
	private $xml;
	private $errors;
	private $config;

	public function __construct($url) {
		$this->url = $url;

	}

	private function load() {
		$this->errors = array();
		$old_setting = libxml_use_internal_errors(true);
		$this->xml = simplexml_load_file($this->url);
		if (!$this->xml) {
			foreach (libxml_get_errors() as $error) {
				$this->errors[] = "Parsing error (line $error->line, column $error->column): $error->message";
			}
		}
		libxml_use_internal_errors($old_setting);
	}

	function parse() {
		$this->config = array();
		$this->load();

		//return $this->xmlToArray($this->xml);
	}

	function getSimpleXML() {
		return $this->xml;
	}

	function getErrors() {
		return $this->errors;
	}

	function getProperty($name) {
		return isset($this->xml->$name) ? $this->xml->$name : NULL;
	}

	function hasProperty($name) {
		return isset($this->xml->$name);
	}

	function getOptions() {
		return $this->xml->options;
	}

	function hasOption($name) {
		foreach ($this->xml->options as $option) {
			if ($option->name == $name) {
				return true;
			}
		}
		return false;
	}

	function getOption($name, $default = NULL) {
		foreach ($this->xml->options as $option) {
			if ($option->name == $name) {
				return $option->value;
			}
		}
		return $default;
	}
};
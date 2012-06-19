<?php


/*
Vemplator 0.6.1 - Making MVC (Model/Vemplator/Controller) a reality
Copyright (C) 2005-2008  Alan Szlosek

See LICENSE for license information.

20071217
- thought about flags to turn off caching ... but this needs more thought
20070819
- tweaks to variable transformation patterns

*/

class Vemplator extends Object{
	private $compileDirectory; // directory to cache template files in. defaults to /tmp/
	private $data; // a stdClass object to hold the data passed to the template
	private $suffix;
	
	private $templateDirectory;
	
	/**
	 * Notable actions:
	 * 	Sets the baseDirectory to the web server's document root
	 * 	Sets default compile path to /tmp/HTTPHOST
	 */
	public function __construct() {
		$this->clear();
		$this->outputModifiers = array ();
	}

	public function setCompileDirectory($dir) {
		$this->compileDirectory = $dir;
	}

	public function setSuffix($v) {
		$this->suffix = $v;
	}

	/**
	 * Assign a template variable.
	 * This can be a single key and value pair, or an associate array of key=>value pairs
	 */
	public function assign($key, $value = '') {
		if (is_array($key)) {
			foreach ($key as $n => $v)
				$this->data-> $n = $v;
		}elseif (is_object($key)) {
			foreach (get_object_vars($key) as $n => $v)
				$this->data-> $n = $v;
		} else {
			$this->data-> $key = $value;
		}
	}
	/**
	 * Alias for assign()
	 */
	public function set($key, $value = '') {
		$this->assign($key, $value);
	}

 
	public function append($key, $value = '') {
		if (!property_exists($this->data, $key)) {
			$this->data-> $key = '';
		}
		$this->data-> $key .= $value;
	}

	public function push($key, $value = null) {
		if (!property_exists($this->data, $key)) {
			$this->data-> $key = array ();
		}
		$data = $this->data-> $key;
		$data[] = $value;
		$this->data-> $key = $data;
	}

	/**
	 * Resets all previously-set template variables
	 */
	public function clear() {
		$this->data = new stdClass;
	}

	/**
	 * In charge of fetching and rendering the specified template
	 */
	public function output($templateDirectory , $template) {
	    $this->templateDirectory = $templateDirectory;
		if (!is_array($template))
			$template = explode('|', $template);
		$out = '';
		$foundTemplate = false;
      
		foreach ($template as $t) {
			$t .= $this->suffix;
			if (file_exists($this->templateDirectory . $t)) {
				$out .= $this->bufferedOutput($templateDirectory, $t);
				$foundTemplate = true;
				break; // found the template, so don't check any more directories
			}
		}

		if (!$foundTemplate){
			$e = str_replace(getcwd(), "", $this->templateDirectory . $this->template);
			trigger_error("Template ($t) not found in  $e", E_USER_ERROR);
 
		}
		return $out;
	}

	/**
	 * Fetches the specified template, finding it in the specified path ... but only after trying to compile it first
	 */
	private function bufferedOutput($path, $template) {
		$compiledFile = $this->compile($path, $template);
		ob_start();
		include ($compiledFile);
		$out = ob_get_clean();
		return $out;
	}

	/**
	 * Compiles the template to PHP code and saves to file ... but only if the template has been updated since the last caching
	 * Uses Regular Expressions to identify template syntax
	 * Passes each match on to the callback for conversion to PHP
	 */
	private function compile($path, $template) {
		// moved from constructor
		 
		if (!file_exists($this->compileDirectory)){
			mkdir($this->compileDirectory,777,true);
		}

		$templateFile = $path . $template;
		
		$compiledFileName = $template.'-'.md5($templateFile);
	
		
		$compiledFile = $this->compileDirectory . $compiledFileName . '.php';

		// don't spend time compiling if nothing has changed
		if (file_exists($compiledFile) && filemtime($compiledFile) >= filemtime($templateFile))
			return $compiledFile;

		$lines = file($templateFile);
		$newLines = array ();
		$matches = null;
		foreach ($lines as $line) {
			$num = preg_match_all('/{--([^{}]+)--}/', $line, & $matches);
			if ($num > 0) {
				for ($i = 0; $i < $num; $i++) {
					$match = $matches[0][$i];
					$new = $this->transformSyntax($matches[1][$i]);
					$line = str_replace($match, $new, $line);
				}
			}
			$newLines[] = $line;
		}
		
		$f = fopen($compiledFile, 'w');
		fwrite($f, "<?php /* simpleword2 Vemplator compile  template ($templateFile)*/ if (!defined(ENTRY)) { exit('Access Denied');} ?>\n".implode('', $newLines));
		fclose($f);
		
		return $compiledFile;
	}

	

	/**
	 * This is where the generation of PHP code takes place
	 */
	private function transformSyntax($input) {
		$from = array (
			//'/(^|\[|,|\(| |\+)([a-zA-Z_][a-zA-Z0-9_]*)($|\W|\.)/',
	//'/(^|\[|,|\(| |\+)([a-zA-Z_][a-zA-Z0-9_]*)($|\W|\.)/',
	'/(^|\[|,|\(|\+| )([a-zA-Z_][a-zA-Z0-9_]*)($|\.|\)|\[|\]|\+)/',
			'/(^|\[|,|\(|\+| )([a-zA-Z_][a-zA-Z0-9_]*)($|\.|\)|\[|\]|\+)/', // again to catch those bypassed by overlapping start/end characters 
	'/\./','/#([a-zA-Z_][a-zA-Z0-9_]*)/'

			
		);
		$to = array (
			'$1$this->data->$2$3',
			'$1$this->data->$2$3',
			'->','$1'
		);

		$parts = explode(':', $input);
		//print_r($parts);
 
		$string = '<?php ';
		switch ($parts[0]) { // check for a template statement
			case 'if' :
				$string .= 'if(' . preg_replace($from, $to, trim($parts[1])) . ') { ';
				break;
			case 'switch' :
				$string .= 'switch(' . preg_replace($from, $to, trim($parts[1])) . ') { ' . ($parts[0] == 'switch' ? 'default: ' : '');
				break;
			case 'foreach' :
				$pieces = explode(',', $parts[1]);
				$string .= 'foreach(' . preg_replace($from, $to, $pieces[0]) . ' as ';
				$string .= preg_replace($from, $to, $pieces[1]);
				if (sizeof($pieces) == 3) // prepares the $value portion of foreach($var as $key=>$value)
					$string .= '=>' . preg_replace($from, $to, $pieces[2]);
				$string .= ') { ';
				break;
			case 'end' :
			case 'endswitch' :
				$string .= '}';
				break;
			case 'else' :
				$string .= '} else {';
				break;
			case 'case' :
				$string .= 'break; case ' . preg_replace($from, $to, $parts[1]) . ':';
				break;
			case 'include' :
				$string .= 'echo $this->output("'.$this->templateDirectory.'","' . $parts[1] . '");';
				//$string .= 'template("' . $parts[1] . '");';
				break;
			default :
				$string .= 'echo ' . preg_replace($from, $to, $parts[0]) . ';';
				break;
		}
		$string .= ' ?>';
		return $string;
	}
}
?>

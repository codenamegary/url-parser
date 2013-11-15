<?php namespace codenamegary\URLParser;

class Batch {

	/**
	 * The actively used URLParser\URL objects.
	 * 
	 * @var array
	 */	
	protected $urls = array();

	/**
	 * When calling methods statically the instance
	 * is stored in this variable.
	 * 
	 * @var object
	 */
	protected static $instance = false;
	
	/**
	 * Base constructor, accepts a url and by default
	 * uses the current full URL of the page being
	 * visited.
	 * 
	 * @param 	string 	$url
	 * @return 	object
	 */
	public function __construct( $urls = array() )
	{
		return $this->_to($urls);
	}

	/*
	 * Compiles and returns the URLs as an array of strings
	 */
	protected function _make()
	{
		$compiledURLs = array();
		foreach($this->urls as $url)
			$compiledURLs[] = $url->make();
		return $compiledURLs;
	}
	
	/**
	 * Loops through the provided array and establishes new URL
	 * objects from each URL.
	 * 
	 * @param	array	$urls
	 * @return	object
	 */
	protected function _to( $urls = array() )
	{
		// If nothing is passed just return the current instance
		if(count($urls) == 0) return $this;
		// Reset the current array of URL objects
		$this->urls = array();
		// Make new URL objects
		foreach($urls as $url) $this->urls[] = new URL($url);
		// Method chaining
		return $this;
	}
	
	// Enables static methods so you can do stuff like...
	//
	//  $url = URLParser\Batch::addQuery( array('userid',12) )->to('http://www.google.ca/?q=stuff');
	//
	public function __callStatic( $method, $arguments )
	{
		$class = get_called_class();
		// Create a new instance (will reset $url)
		static::$instance = new $class;
		if($method == "to") return call_user_func_array( array(static::$instance, "_$method"), $arguments );
		foreach($this->urls as $url)
			call_user_func_array( array( $url, "$method" ) , $arguments );
		return static::$instance;
	}

	public function __call( $method, $arguments )
	{
		if($method == "make" || $method == "to") return call_user_func_array( array($this, "_$method"), $arguments );
		foreach($this->urls as $url)
			call_user_func_array( array( $url, "$method" ) , $arguments );			
		return $this;
	}

}
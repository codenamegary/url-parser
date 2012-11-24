<?php

namespace URLParser;

class URL {

	/**
	 * The actively used URL in the format dictated by
	 * PHP's built-in parse_url function. This is the
	 * property which is actively used and modified
	 * throughout the functions of this class.
	 * 
	 * @var array
	 */	
	protected $url = array(
		'scheme'	=> 'http',
		'host'		=> '',
		'port'		=> false,
		'user'		=> '',
		'pass'		=> '',
		'path'		=> '',
		'query'		=> '',
		'fragment'	=> '',
	);
	
	/**
	 * Same as $url but always contains a blank array,
	 * used by the _to() method to reset the current
	 * URL property when overwriting with a new
	 * base URL.
	 * 
	 * @var array
	 */
	protected $resetURL = array(
		'scheme'	=> 'http',
		'host'		=> '',
		'port'		=> false,
		'user'		=> '',
		'pass'		=> '',
		'path'		=> '',
		'query'		=> '',
		'fragment'	=> '',
	);
	
	/**
	 * When calling methods statically the instance
	 * is stored in this variable.
	 * 
	 * @var object
	 */
	protected static $instance = false;
	
	/**
	 * Base constructor, accepts a url or by default
	 * uses the current full URL of the page being
	 * visited.
	 * 
	 * @param 	string 	$url
	 * @return 	object
	 */
	public function __construct( $url = false )
	{
		// Call the _to method with either the full current page url
		// (when $url is false) or the value of $url
		return $this->_to(($url == false) ? $this->_currentURL() : $url );
	}

	/**
	 * Parses and returns the full URL of the page
	 * currently being visited. Used by the
	 * __construct method.
	 * 
	 * @return	string
	 */
	protected function _currentURL()
	{
		if(!isset($_SERVER['REQUEST_URI'])){
			$serverrequri = $_SERVER['PHP_SELF'];
		}else{
			$serverrequri =    $_SERVER['REQUEST_URI'];
		}
	    $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
	    $protocol = explode("/",$_SERVER["SERVER_PROTOCOL"]);
		$protocol = strtolower($protocol[0]).strtolower($s);
	    $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
	    return $protocol."://".$_SERVER['HTTP_HOST'].$port.$serverrequri;   
	}

	/**
	 * Swaps the base URL the parser is operating on for the
	 * URL passed in $url. This is useful for scenarios where
	 * you wish to batch add query strings to multiple URLs.
	 * You can setup the queries on one object and then _to()
	 * and _make() multiple calls to generate new URLs.
	 * 
	 * @param	string	$url
	 * @return	object
	 */
	protected function _to( $url = false)
	{
		// If nothing is passed just return the current instance
		if($url == false) return $this;
		$this->url = array_merge( $this->resetURL, parse_url( $url ) );
		// Method chaining
		return $this;
	}

	/**
	 * Returns the query string for the current URL as a $key => $value array.
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.powrit.com?val1=products&val2=services');
	 * var_dump($url->queryArray());
	 * 
	 * Outputs...
	 * 
	 * array(2) {
  	 *		["val1"]=>
  	 *		string(8) "products"
  	 *		["val2"]=>
  	 *		string(8) "services"
	 *	}
	 *  
	 * @return	array
	 */
	protected function _queryArray()
	{
		parse_str( $this->url['query'], $q );
		return $q;
	}

	
	/**
	 * Returns the query string for the current URL or, if
	 * $query is specified returns the value for that parameter
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.powrit.com?val1=products&val2=services');
	 * echo $url->query('val1') . "<br/>";
	 * echo $url->query('val2') . "<br/>";
	 * echo $url->query('val3') . "<br/>";   // Returns FALSE
	 * echo $url->query() . "<br/>";
	 * 
	 * Outputs...
	 * 
	 * 		products
	 * 		services
	 * 		
	 * 		val1=products&val2=services
	 *  
	 * @param	string	$query
	 * @return	string
	 */
	protected function _query( $query = false )
	{
		// Splits the query parameter ($_GET vars) of the base URL
		// into a $key => $value array and stores in $q
		parse_str( $this->url['query'], $q );

		// Make and return the query string if $query == false
		if($query == false) return http_build_query($q);
		
		// Return false or the query val if it does exist
		return isset($q[$query]) ? $q[$query] : false ;
	}
			
	/**
	 * Appends a $key => $value array into the URL's
	 * query string.
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.powrit.com');
	 * $url->addQuery(array(
	 * 		'val1'		=> 'stuff',
	 * 		'val2'		=> 'more stuff',
	 * ));
	 * echo $url->make();
	 * 
	 * Outputs...
	 * 
	 * 		http://www.powrit.com?val1=stuff&val2=more+stuff
	 * 
	 * @param	array	$query
	 * @return	object
	 */
	protected function _addQuery( $query = array() )
	{
		$q = array_merge( $this->_queryArray(), $query );
		$this->url['query'] = http_build_query( $q );
		return $this;
	}
	
	/**
	 * Strips a query called "$query" from the query string.
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.powrit.com?val1=products&val2=services');
	 * $url->stripQuery('val1');
	 * echo $url->make();
	 * 
	 * Outputs...
	 * 
	 * 		http://www.powrit.com?val2=services
	 * 
	 * @param	string	$query
	 * @return	object
	 */
	protected function _stripQuery( $query )
	{
		$queries = $this->_queryArray();
		foreach( $queries as $key => $q )
			if( $key == $query ) unset( $queries[$key] );
		$this->url['query'] = http_build_query( $queries );
		return $this;
	}

	/**
	 * Strips the ENTIRE query string from the url.
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.powrit.com?val1=products&val2=services');
	 * $url->stripQueries();
	 * echo $url->make();
	 * 
	 * Outputs...
	 * 
	 * 		http://www.powrit.com
	 * 
	 * @return	object
	 */
	protected function _stripQueries()
	{
		$this->url['query'] = '';
		return $this;
	}

	/**
	 * Renames a query parameter from $oldName to $newName 
	 *
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.powrit.com?val1=products&val2=services');
	 * $url->swapQuery('val1','valX');
	 * echo $url->make();
	 * 
	 * Outputs...
	 * 
	 * 		http://www.powrit.com?val2=services&valX=products
	 *  
	 * @param	string	$old
	 * @param	string	$new
	 * @return	object
	 */
	protected function _swapQuery( $oldName, $newName )
	{
		$queries = $this->_queryArray();
		foreach( $queries as $key => $q )
			if( $key == $oldName ) { unset( $queries[$key] ); $queries[$newName] = $q; }
		$this->url['query'] = http_build_query( $queries );
		return $this;
	}

	/**
	 * Returns the segments (path) string for the current URL as an array.
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.powrit.com/products/services');
	 * var_dump($url->segmentArray());
	 * 
	 * Outputs...
	 * 
	 * array(2) {
  	 *		[0]=>
  	 *		string(8) "products"
  	 *		[1]=>
  	 *		string(8) "services"
	 *	}
	 *  
	 * @return	array
	 */
	protected function _segmentArray()
	{
		if(substr($this->url['path'], 0,1)=="/") $this->url['path'] = substr($this->url['path'],1);
		return explode( "/", $this->url['path'] );
	}
	
	/**
	 * Returns the the entire segment path if $search is false or
	 * looks for $search in the URL path and returns true / false.
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.powrit.com/products/services');
	 * echo $url->segments('products') . "<br/>";
	 * echo $url->segments('services') . "<br/>";
	 * echo $url->segments('abcd') . "<br/>";   // Returns FALSE
	 * echo $url->segments() . "<br/>";
	 * 
	 * Outputs...
	 * 
	 * 		(true)
	 * 		(true)
	 * 		(false)
	 * 		/products/services
	 *  
	 * @param	string	$search
	 * @return	mixed
	 */
	protected function _segments( $search = false )
	{
		// If we're searching for a segment, find it and return true or false
		if(!$search == false && in_array($search, $this->_segmentArray())) return true;
		if(!$search == false && !in_array($search, $this->_segmentArray())) return false;

		$segments = count($this->_segmentArray()) > 0 ? "/" . implode("/",$this->_segmentArray()) : '';
		
		// If we're not searching just return the segment string
		if($search == false) return $segments;
	}
	
	/**
	 * Appends a specified text segment to the URL path. Optionally
	 * a segment can be specified ($appendAfter) and the new segment
	 * will be inserted immediately following the instance of
	 * $appendAfter.
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.powrit.com/products/services');
	 * $url->appendSegment('test');
	 * echo $url->make() . "<br/>";
	 * $url->appendSegment('argh','products');
	 * echo $url->make() . "<br/>";
	 * 
	 * Outputs...
	 * 
	 * 		http://www.powrit.com/products/services/test
	 * 		http://www.powrit.com/products/argh/services/test
	 *  
	 * @param	string	$newSegment
	 * @param	string	$appendAfter
	 * @return	object
	 */
	protected function _appendSegment( $newSegment, $appendAfter = false )
	{
		$segments = explode( "/", $this->url['path'] );
		$arrStart = $segments;
		$arrEnd = array();
		if($appendAfter !== false)
		{
			// Find the key of $appendAfter if it's not false
			$key = array_search( $appendAfter, $segments );
			// If it exists...
			if($key !== false)
			{
				// "apple" - "banana" - "coconut" - "date"
				$arrStart = array_slice($segments, 0, $key+1);
				$arrEnd = array_slice($segments, $key+1);
			}
		}
		
		$segments = array_merge($arrStart,array($newSegment),$arrEnd);
		$this->url['path'] = count($segments) > 0 ? implode( "/", $segments ) : '' ;
		return $this;
	}

	/**
	 * Prepends a specified text segment to the URL path. Optionally
	 * a segment can be specified ($prependBefore) and the new segment
	 * will be inserted immediately before the instance of
	 * $prependBefore.
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.powrit.com/products/services');
	 * $url->prependSegment('test');
	 * echo $url->make() . "<br/>";
	 * $url->prependSegment('argh','products');
	 * echo $url->make() . "<br/>";
	 * 
	 * Outputs...
	 * 
	 * 		http://www.powrit.com/test/products/services
	 * 		http://www.powrit.com/test/argh/products/services
	 *  
	 * @param	string	$newSegment
	 * @param	string	$prependBefore
	 * @return	object
	 */
	protected function _prependSegment( $newSegment, $prependBefore = false )
	{
		$segments = $this->_segmentArray();
		$arrStart = array();
		$arrEnd = $segments;
		if($prependBefore !== false)
		{
			// Find the key of $appendAfter if it's not false
			$key = array_search( $prependBefore, $segments );
			// If it exists...
			if($key !== false)
			{
				// "apple" - "banana" - "coconut" - "date"
				$arrStart = array_slice($segments, 0, $key);
				$arrEnd = array_slice($segments, $key);
			}
		}
		
		$segments = array_merge($arrStart,array($newSegment),$arrEnd);
		$this->url['path'] = count($segments) > 0 ? implode( "/", $segments ) : '' ;
		return $this;
	}

	/**
	 * Strips a segment called $s from the URI.
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.powrit.com/products/services');
	 * $url->stripSegment('products');
	 * echo $url->make();
	 * 
	 * Outputs...
	 * 
	 * 		http://www.powrit.com/services
	 * 
	 * @param	string	$s
	 * @return	object
	 */
	protected function _stripSegment( $s )
	{
		// Parse the base URL segments into an array, pop if $s is in there
		$segments = $this->_segmentArray();
		foreach( $segments as $key => $segment )
			if( $segment == $s ) unset( $segments[$key] );
		$this->url['path'] = count($segments) > 0 ? implode( "/", $segments ) : '' ;

		return $this;
	}
	
	/**
	 * Strips all segments from the URL
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.powrit.com/products/services?val1=stuff&val2=more+stuff');
	 * $url->stripSegments();
	 * echo $url->make();
	 * 
	 * Outputs...
	 * 
	 * 		http://www.powrit.com?val1=stuff&val2=more+stuff
	 * 
	 * @return	object
	 */
	protected function _stripSegments()
	{
		$this->url['path'] = '' ;
		return $this;
	} 
	
	/**
	 * Swaps segment names using $key => $value input where
	 * $oldname => $newname.
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.powrit.com/oldSegment/home');
	 * $url->swapSegments(array(
	 * 		'oldSegment'	=> 'newSegment',
	 * 		'home'			=> 'main',
	 * ));
	 * echo $url->make();
	 * 
	 * Outputs...
	 * 
	 * 		http://www.powrit.com/newSegment/main
	 *  
	 * @param	array	$newSegments
	 * @return	object
	 */
	protected function _swapSegment( $newSegments = array() )
	{
		$segments = $this->_segmentArray();
		// Loop through every segment
		foreach($segments as &$segment)
			// Loop through input
			foreach($newSegments as $oldSegment => $newSegment)
				// If input matches, change it.
				if($segment == $oldSegment) $segment = $newSegment;
		// Implde segments
		$this->url['path'] = implode("/",$segments);
		return $this;
	}
	
	/**
	 * Sets or returns the anchor tag at the end of the URL
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.powrit.com');
	 * $url->anchor('products');
	 * echo $url->make();
	 * 
	 * Outputs...
	 * 
	 * 		http://www.powrit.com/#products
	 *  
	 * @param	string	$anchor
	 * @return	mixed
	 */
	protected function _anchor( $anchor = false )
	{
		if($anchor === false) return $this->url['fragment'];
		$this->url['fragment'] = $anchor;
		return $this;
	}
	
	/**
	 * Sets or returns the protocol for the URL (http/ftp/etc.).
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.powrit.com');
	 * $url->protocol('ftp');
	 * echo $url->make();
	 * 
	 * Outputs...
	 * 
	 * 		ftp://www.powrit.com
	 *  
	 * @param	string	$protocol
	 * @return	mixed
	 */
	protected function _protocol( $protocol = false )
	{
		if($protocol === false) return $this->url['scheme'];
		$this->url['scheme'] = $protocol;
		return $this;
	}
	
	/**
	 * Sets or returns the host (domain) for the url.
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.powrit.com/stuff?val1=foo');
	 * $url->host('www.google.ca');
	 * echo $url->make();
	 * 
	 * Outputs...
	 * 
	 * 		http://www.google.ca/stuff?val1=foo
	 *  
	 * @param	string	$host
	 * @return	mixed
	 */
	protected function _host( $host = false )
	{
		if($host === false) return $this->url['host'];
		$this->url['host'] = $host;
		return $this;
	}

	/**
	 * Sets or returns the port for the url.
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.powrit.com/stuff?val1=foo');
	 * $url->port(8080);
	 * echo $url->make();
	 * 
	 * Outputs...
	 * 
	 * 		http://www.powrit.com:8080/stuff?val1=foo
	 *  
	 * @param	string	$port
	 * @return	mixed
	 */
	protected function _port( $port = false )
	{
		if($port === false) return $this->url['port'];
		$this->url['port'] = $port;
		return $this;
	}
	
	/**
	 * Sets or returns the user for auth.
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.powrit.com/stuff?val1=foo');
	 * $url->user('joe');
	 * echo $url->make();
	 * 
	 * Outputs...
	 * 
	 * 		http://joe@www.powrit.com/stuff?val1=foo
	 *  
	 * @param	string	$user
	 * @return	mixed
	 */
	protected function _user( $user = false )
	{
		if($user === false) return $this->url['user'];
		$this->url['user'] = $user;
		return $this;
	}
	
	/**
	 * Sets or returns the password for auth. User must be specified first.
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.powrit.com/stuff?val1=foo');
	 * $url->user('joe');
	 * $url->pass('foobar');
	 * echo $url->make();
	 * 
	 * Outputs...
	 * 
	 * 		http://joe:foobar@www.powrit.com/stuff?val1=foo
	 *  
	 * @param	string	$pass
	 * @return	mixed
	 */
	protected function _pass( $pass = false )
	{
		if($pass === false) return $this->url['pass'];
		$this->url['pass'] = $pass;
		return $this;
	}
	


	/*
	 * Compiles and returns the URL as a string
	 */
	protected function _make()
	{
		
		$q = $this->_query();
		$s = $this->_segments();
		
		$newURL = 
			$this->url['scheme'] . "://" .
			$this->url['user'] .
			( ($this->url['pass'] == "") ? "" : ":" . $this->url['pass'] ) .
			( ($this->url['user'] == "") ? "" : "@" ) .
			$this->url['host'] .
			( ($this->url['port'] == false) ? '' : ":" . $this->url['port'] ) .
			$s .
			( (strlen($q) > 0) ? "?" . $q : "" ) .
			( (strlen($this->url['fragment']) > 0) ? "#" . $this->url['fragment'] : "" );

		return $newURL;
	}
	
	// Enables static methods so you can do stuff like...
	//
	//  $url = URLParser::addQuery( array('userid',12) )->to('http://www.google.ca/?q=stuff');
	//
	public function __callStatic( $method, $arguments )
	{
		$class = get_called_class();
		// Create a new instance (will reset $url)
		static::$instance = new $class;
		return call_user_func_array( array( static::$instance, "_$method" ) , $arguments );
	}

	public function __call( $method, $arguments )
	{
		return call_user_func_array( array( $this, "_$method" ) , $arguments );
	}

}
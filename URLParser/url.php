<?php namespace URLParser;

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
	protected function currentURL()
	{
		if( !isset( $_SERVER['REQUEST_URI'] ) )	$requestUri = $_SERVER['PHP_SELF'];
		else $requestUri = $_SERVER['REQUEST_URI'];
		$s = empty( $_SERVER["HTTPS"] ) ? '' : ( $_SERVER["HTTPS"] == "on" ) ? "s" : "";
		$protocol = explode( "/", $_SERVER["SERVER_PROTOCOL"] );
		$protocol = strtolower( $protocol[0] ) . strtolower( $s );
		$port = ( $_SERVER["SERVER_PORT"] == "80" ) ? "" : ( ":" . $_SERVER["SERVER_PORT"] );
		return $protocol . "://" . $_SERVER['HTTP_HOST'] . $port.$requestUri;
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
	public function to( $url = false)
	{
		// If nothing is passed just return the current instance
		if( $url == false ) return $this;
		$this->url = array_merge( $this->resetURL, parse_url( $url ) );
		// Method chaining
		return $this;
	}

	/**
	 * Returns the query string for the current URL as a $key => $value array.
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.example.com?val1=products&val2=services');
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
	public function queryArray()
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
	 * $url = new URLParser\URL('http://www.example.com?val1=products&val2=services');
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
	public function query( $query = false )
	{
		// Splits the query parameter ($_GET vars) of the base URL
		// into a $key => $value array and stores in $q
		parse_str( $this->url['query'], $q );

		// Make and return the query string if $query == false
		if( $query == false ) return http_build_query( $q );
		
		// Return false or the query val if it does exist
		return isset( $q[$query] ) ? $q[$query] : false ;
	}
			
	/**
	 * Appends a $key => $value array into the URL's
	 * query string.
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.example.com');
	 * $url->addQuery(array(
	 * 		'val1'		=> 'stuff',
	 * 		'val2'		=> 'more stuff',
	 * ));
	 * echo $url->make();
	 * 
	 * Outputs...
	 * 
	 * 		http://www.example.com?val1=stuff&val2=more+stuff
	 * 
	 * @param	array	$query
	 * @return	object
	 */
	public function addQuery( $query = array() )
	{
		$q = array_merge( $this->queryArray(), $query );
		$this->url['query'] = http_build_query( $q );
		return $this;
	}
	
	/**
	 * Strips a query called "$query" from the query string.
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.example.com?val1=products&val2=services');
	 * $url->stripQuery('val1');
	 * echo $url->make();
	 * 
	 * Outputs...
	 * 
	 * 		http://www.example.com?val2=services
	 * 
	 * @param	string	$query
	 * @return	object
	 */
	public function stripQuery( $query )
	{
		$queries = $this->queryArray();
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
	 * $url = new URLParser\URL('http://www.example.com?val1=products&val2=services');
	 * $url->stripQueries();
	 * echo $url->make();
	 * 
	 * Outputs...
	 * 
	 * 		http://www.example.com
	 * 
	 * @return	object
	 */
	public function stripQueries()
	{
		$this->url['query'] = '';
		return $this;
	}

	/**
	 * Renames a query parameter from $oldName to $newName 
	 *
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.example.com?val1=products&val2=services');
	 * $url->swapQuery('val1','valX');
	 * echo $url->make();
	 * 
	 * Outputs...
	 * 
	 * 		http://www.example.com?val2=services&valX=products
	 *  
	 * @param	string	$old
	 * @param	string	$new
	 * @return	object
	 */
	public function swapQuery( $oldName, $newName )
	{
		$queries = $this->renameArrayKey( $this->queryArray(), $oldName, $newName );
		$this->url['query'] = http_build_query( $queries );
		return $this;
	}

	/**
	 * Lookups up $oldKey in $array and renames the key to $newKey
	 * 
	 * eg...
	 * 
	 * $arr = array(
	 * 		'foo'		=> 'bar',
	 * 		'foo2'		=> 'bar2',
	 * );
	 * 
	 * $arr = $this->renameArrayKey( $arr, 'foo', 'test' );
	 * 
	 * returns...
	 * 
	 * array(
	 * 		'test'		=> 'bar',
	 * 		'foo2'		=> 'bar2',
	 * )
	 * 
	 * @param	array 	$array
	 * @param	string	$oldKey
	 * @param	string	$newKey
	 * @return	array
	 */
	protected function renameArrayKey( $array = array(), $oldKey, $newKey )
	{
		// If oldKey isn't set just return the passed array, unmodified
		if( !isset( $array[$oldKey] ) ) return $array;
		$keys = array_keys( $array );
		$keys[$oldKey] = $newKey;
		return array_combine( $keys, array_values( $array ) );
	}
	
	/**
	 * Plural of renameArrayKey, allows passing of oldKey => newKey as an array.
	 * 
	 * eg...
	 * 
	 * $arr = array(
	 * 		'foo'		=> 'bar',
	 * 		'foo2'		=> 'bar2',
	 * );
	 * 
	 * $arr = $this->renameArrayKey( $arr, array( 'foo' => 'test' ) );
	 * 
	 * returns...
	 * 
	 * array(
	 * 		'test'		=> 'bar',
	 * 		'foo2'		=> 'bar2',
	 * )
	 * 
	 * @param	array 	$array
	 * @param	array	$newKeys
	 * @return	array
	 */
	protected function renameArrayKeys( $array = array(), $newKeys )
	{
		foreach( $newKeys as $oldKey => $newKey ) $array = $this->renameArrayKey( $oldKey, $newKey );
	}

	/**
	 * Returns the segments (path) string for the current URL as an array.
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.example.com/products/services');
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
	public function segmentArray()
	{
		if( substr( $this->url['path'], 0, 1 ) == "/" ) $this->url['path'] = substr($this->url['path'], 1 );
		return explode( "/", $this->url['path'] );
	}
	
	/**
	 * Returns the the entire segment path if $search is false or
	 * looks for $search in the URL path and returns true / false.
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.example.com/products/services');
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
	public function segments( $search = false )
	{
		// If we're searching for a segment, find it and return true or false
		if( !$search == false && in_array( $search, $this->segmentArray() ) ) return true;
		if( !$search == false && !in_array( $search, $this->segmentArray() ) ) return false;

		$segments = count( $this->segmentArray() ) > 0 ? "/" . implode( "/", $this->segmentArray() ) : '';
		
		// If we're not searching just return the segment string
		if( $search == false ) return $segments;
	}
	
	/**
	 * Takes an array and returns an array of 2 arrays.
	 * 
	 * - One array including everything BEFORE the specified key
	 * - Another array INCLUDING the specified key and everything AFTER
	 * 
	 * Optionally specify "$offset" to indicate that the slice should occur AFTER
	 * $offset keys, meaning the function will return...
	 * 
	 * - One array including everything up to and INCLUDING the specified key
	 *   plus $offset keys later
	 * - Another array including everything AFTER the specified key plus $offset keys
	 * 
	 * In the case that $key does not exist or is not specified the function will
	 * still return 2 arrays. If $offset is not specified or is less than 0 (prepend mode),
	 * the returned array at index 0 will contain a blank array and index 1 will contain
	 * all of the elements from the original array. If $offset is specified and is
	 * greater than 0 (append mode) index 0 will contain all of the elements from the
	 * original array and index 1 will contain a blank array.
	 * 
	 * 
	 * ----------------------------------------------------------------------------------
	 * Example 1:
	 * 
	 * $arr = array(
	 * 		'key1'		=> 'val1',
	 * 		'key2'		=> 'val2',
	 * 		'key3'		=> 'val3',
	 * 		'key4'		=> 'val4',
	 * 		'key5'		=> 'val5',
	 * );
	 * 
	 * Note: $offset not specificed (0) means the function operates in "prepend" mode
	 * 
	 * $arr = $this->sliceArrayAtKey( $arr, 'key2' );
	 * 
	 * returns....
	 * 
	 * array(
	 * 		[0]			=> array(
	 * 			'key1'		=> 'val1',
	 * 		),
	 * 		[1]			=> array(
	 * 			'key2'		=> 'val2',
	 *	 		'key3'		=> 'val3',
	 * 			'key4'		=> 'val4',
	 * 			'key5'		=> 'val5',
	 * 		),
	 * )
	 * 
	 * 
	 * ----------------------------------------------------------------------------------
	 * Example 2:
	 * 
	 * $arr = array(
	 * 		'key1'		=> 'val1',
	 * 		'key2'		=> 'val2',
	 * 		'key3'		=> 'val3',
	 * 		'key4'		=> 'val4',
	 * 		'key5'		=> 'val5',
	 * );
	 * 
	 * Note: $offset > 0 (1) means the function operates in "append" mode
	 * 
	 * $arr = $this->sliceArrayAtKey( $arr, 'key2', 1 );
	 * 
	 * returns....
	 * 
	 * array(
	 * 		[0]			=> array(
	 * 			'key1'		=> 'val1',
	 * 			'key2'		=> 'val2',
	 * 		),
	 * 		[1]			=> array(
	 *	 		'key3'		=> 'val3',
	 * 			'key4'		=> 'val4',
	 * 			'key5'		=> 'val5',
	 * 		),
	 * )
	 * 
	 * 
	 * ----------------------------------------------------------------------------------
	 * Example 3:
	 * 
	 * $arr = array(
	 * 		'key1'		=> 'val1',
	 * 		'key2'		=> 'val2',
	 * 		'key3'		=> 'val3',
	 * 		'key4'		=> 'val4',
	 * 		'key5'		=> 'val5',
	 * );
	 * 
	 * $arr = $this->sliceArrayAtKey( $arr, 'key2', 3 );
	 * 
	 * returns....
	 * 
	 * array(
	 * 		[0]			=> array(
	 * 			'key1'		=> 'val1',
	 * 			'key2'		=> 'val2',
	 * 			'key3'		=> 'val3',
	 * 			'key4'		=> 'val4',
	 * 		),
	 * 		[1]			=> array(
	 * 			'key5'		=> 'val5',
	 * 		),
	 * )
	 * 
	 * 
	 * ----------------------------------------------------------------------------------
	 * Example 4:
	 * 
	 * $arr = array(
	 * 		'key1'		=> 'val1',
	 * 		'key2'		=> 'val2',
	 * 		'key3'		=> 'val3',
	 * 		'key4'		=> 'val4',
	 * 		'key5'		=> 'val5',
	 * );
	 * 
	 * $arr = $this->sliceArrayAtKey( $arr, 'key2', -1 );
	 * 
	 * returns....
	 * 
	 * array(
	 * 		[0]			=> array(
	 * 		),
	 * 		[1]
	 * 			'key1'		=> 'val1',
	 * 			'key2'		=> 'val2',
	 * 			'key3'		=> 'val3',
	 * 			'key3'		=> 'val4',
	 * 			'key5'		=> 'val5',
	 * 		),
	 * )
	 * 
	 * 
	 * ----------------------------------------------------------------------------------
	 * Example 5:
	 * 
	 * Note: $offset is not specified and the function defaults to "prepend" mode, $key
	 * 		 does not exist.
	 * 
	 * $arr = array(
	 * 		'key1'		=> 'val1',
	 * 		'key2'		=> 'val2',
	 * 		'key3'		=> 'val3',
	 * 		'key4'		=> 'val4',
	 * 		'key5'		=> 'val5',
	 * );
	 * 
	 * $arr = $this->sliceArrayAtKey( $arr, 'key6' );
	 * 
	 * returns....
	 * array(
	 * 		[0]			=> array(
	 * 		),
	 * 		[1]
	 * 			'key1'		=> 'val1',
	 * 			'key2'		=> 'val2',
	 * 			'key3'		=> 'val3',
	 * 			'key3'		=> 'val4',
	 * 			'key5'		=> 'val5',
	 * 		),
	 * )
	 * 
	 * 
	 * ----------------------------------------------------------------------------------
	 * Example 6:
	 * 
	 * Note: $offset is 1 and the function defaults to "append" mode, $key does not exist
	 * 
	 * $arr = array(
	 * 		'key1'		=> 'val1',
	 * 		'key2'		=> 'val2',
	 * 		'key3'		=> 'val3',
	 * 		'key4'		=> 'val4',
	 * 		'key5'		=> 'val5',
	 * );
	 * 
	 * $arr = $this->sliceArrayAtKey( $arr, 'key6', 1 );
	 * 
	 * returns....
	 * array(
	 * 		[0]			=> array(
	 * 			'key1'		=> 'val1',
	 * 			'key2'		=> 'val2',
	 * 			'key3'		=> 'val3',
	 * 			'key3'		=> 'val4',
	 * 			'key5'		=> 'val5',
	 * 		),
	 * 		[1]
	 * 		),
	 * )
	 * 
	 * @param	array	$array
	 * @param	string	$key
	 * @param	boolean	$offset
	 * @return	array
	 */
	protected function sliceArrayAtKey( $array, $key = false, $offset = 0 )
	{
		$arrStart = ( $offset > 0 ) ? $array : array() ;
		$arrEnd = ( $offset <= 0 ) ? $array : array() ;
		$arrKeys = array_keys( $array );
		$key = $key ? array_search( $key, $arrKeys ) : false ;
		if( $key !== false )
		{
			$arrStart = array_slice( $array, 0, $key + $offset );
			$arrEnd = array_slice( $array, $key + $offset );
		}
		$array = array(
			$arrStart,
			$arrEnd,
		);
		return $array;
	}
	
	
	
	/*
	 * ----------------------------------------------------------------------------------
	 * 		See the documentation and examples on sliceArrayAtKey for additional info
	 * 		on how $array is sliced and processed.
	 * ----------------------------------------------------------------------------------
	 * 
	 * Takes $array, $key, and an array of $newKeyValuePairs and splices
	 * them into $array at the location of $key. Optionally takes a $offset
	 * and inserts at (position of $key) + $offset.
	 * 
	 * You may specify a negative value for $offset to insert at previous $offset
	 * number keys.
	 * 
	 * Note: $offset is used to determine prepend vs. append mode (see docs and
	 * examples on sliceArrayAtKey for additional info).
	 * 
	 * If $key does not exist and $offset is less than or equal to 0
	 * $newKeyValuePairs will be prepended to $array.
	 * 
	 * If $key does not exist and $offset is greater than 0 $newKeyValuePairs will
	 * be appended to $array. 
	 * 
	 * 
	 * ----------------------------------------------------------------------------------
	 * Example 1:
	 * 
	 * $arr = array(
	 * 		'key1'		=> 'val1',
	 * 		'key2'		=> 'val2',
	 * 		'key3'		=> 'val3',
	 * 		'key4'		=> 'val4',
	 * 		'key5'		=> 'val5',
	 * );
	 * 
	 * $newKeyValuePairs = arrray(
	 * 		'keyX'		=> 'valX',
	 * 		'keyY'		=> 'valY',
	 * );
	 * 
	 * Note: $offset is not specified and function defaults to "prepend" mode
	 * 
	 * $arr = $this->insertIntoArrayAtKey( $arr, 'key2', $newKeyValuePairs );
	 * 
	 * returns....
	 * 
	 * array(
	 *		'key1'		=> 'val1',
	 * 		'keyX'		=> 'valX',	<--- New values inserted BEFORE key2
	 * 		'keyY'		=> 'valY',	<--- New values inserted BEFORE key2
	 *		'key2'		=> 'val2',
	 *		'key3'		=> 'val3',
	 *		'key4'		=> 'val4',
	 *		'key5'		=> 'val5',
	 * )
	 * 
	 * 
	 * 
	 * ----------------------------------------------------------------------------------
	 * Example 2:
	 * 
	 * $arr = array(
	 * 		'key1'		=> 'val1',
	 * 		'key2'		=> 'val2',
	 * 		'key3'		=> 'val3',
	 * 		'key4'		=> 'val4',
	 * 		'key5'		=> 'val5',
	 * );
	 * 
	 * $newKeyValuePairs = arrray(
	 * 		'keyX'		=> 'valX',
	 * 		'keyY'		=> 'valY',
	 * );
	 * 
	 * Note: $offset is specified as 1 and function defaults to "append" mode
	 * 
	 * $arr = $this->insertIntoArrayAtKey( $arr, 'key2', $newKeyValuePairs, 1 );
	 * 
	 * returns....
	 * 
	 * array(
	 *		'key1'		=> 'val1',
	 *		'key2'		=> 'val2',
	 * 		'keyX'		=> 'valX',	<--- New values inserted AFTER key2
	 * 		'keyY'		=> 'valY',	<--- New values inserted AFTER key2
 	 *		'key3'		=> 'val3',
	 *		'key4'		=> 'val4',
	 *		'key5'		=> 'val5',
	 * )
	 * 
	 * @param	array	$array
	 * @param	string	$key
	 * @param	array	$newKeyValuePairs
	 * @param	int		$offset
	 * @return	array
	 */
	protected function insertIntoArrayAtKey( $array, $key, $newKeyValuePairs, $offset = 0 )
	{
		$slices = $this->sliceArrayAtKey( $array, $key, $offset );
		if( !$slices ) return false;
		return array_merge( $slices[0], $newKeyValuePairs, $slices[1] );
	}

	/**
	 * Appends a specified text segment to the URL path. Optionally
	 * a segment can be specified ($appendAfter) and the new segment
	 * will be inserted immediately following the instance of
	 * $appendAfter.
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.example.com/products/services');
	 * $url->appendSegment('test');
	 * echo $url->make() . "<br/>";
	 * $url->appendSegment('argh','products');
	 * echo $url->make() . "<br/>";
	 * 
	 * Outputs...
	 * 
	 * 		http://www.example.com/products/services/test
	 * 		http://www.example.com/products/argh/services/test
	 *  
	 * @param	string	$newSegment
	 * @param	string	$appendAfter
	 * @return	object
	 */
	public function appendSegment( $newSegment, $appendAfter = false )
	{
		$segments = $this->insertIntoArrayAtKey( $this->segmentArray(), $appendAfter, array( $newSegment ), 1 );
		$this->url['path'] = count( $segments ) > 0 ? implode( "/", $segments ) : '' ;
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
	 * $url = new URLParser\URL('http://www.example.com/products/services');
	 * $url->prependSegment('test');
	 * echo $url->make() . "<br/>";
	 * $url->prependSegment('argh','products');
	 * echo $url->make() . "<br/>";
	 * 
	 * Outputs...
	 * 
	 * 		http://www.example.com/test/products/services
	 * 		http://www.example.com/test/argh/products/services
	 *  
	 * @param	string	$newSegment
	 * @param	string	$prependBefore
	 * @return	object
	 */
	public function prependSegment( $newSegment, $prependBefore = false )
	{
		$segments = $this->insertIntoArrayAtKey( $this->segmentArray(), $prependBefore, array( $newSegment ) );
		$this->url['path'] = count( $segments ) > 0 ? implode( "/", $segments ) : '' ;
		return $this;
	}

	/**
	 * Strips a segment called $s from the URI.
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.example.com/products/services');
	 * $url->stripSegment('products');
	 * echo $url->make();
	 * 
	 * Outputs...
	 * 
	 * 		http://www.example.com/services
	 * 
	 * @param	string	$s
	 * @return	object
	 */
	public function stripSegment( $s )
	{
		// Parse the base URL segments into an array, pop if $s is in there
		$segments = $this->segmentArray();
		if( isset( $segments[$s] ) ) unset( $segments[$s] );
		$this->url['path'] = count($segments) > 0 ? implode( "/", $segments ) : '' ;
		return $this;
	}
	
	/**
	 * Strips all segments from the URL
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.example.com/products/services?val1=stuff&val2=more+stuff');
	 * $url->stripSegments();
	 * echo $url->make();
	 * 
	 * Outputs...
	 * 
	 * 		http://www.example.com?val1=stuff&val2=more+stuff
	 * 
	 * @return	object
	 */
	public function stripSegments()
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
	 * $url = new URLParser\URL('http://www.example.com/oldSegment/home');
	 * $url->swapSegments(array(
	 * 		'oldSegment'	=> 'newSegment',
	 * 		'home'			=> 'main',
	 * ));
	 * echo $url->make();
	 * 
	 * Outputs...
	 * 
	 * 		http://www.example.com/newSegment/main
	 *  
	 * @param	array	$newSegments
	 * @return	object
	 */
	public function swapSegments( $newSegments = array() )
	{
		$segments = $this->renameArrayKeys( $this->segmentArray(), $newSegments );
		$this->url['path'] = implode( "/", $segments );
		return $this;
	}
	
	/**
	 * Sets or returns the anchor tag at the end of the URL
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.example.com');
	 * $url->anchor('products');
	 * echo $url->make();
	 * 
	 * Outputs...
	 * 
	 * 		http://www.example.com/#products
	 *  
	 * @param	string	$anchor
	 * @return	mixed
	 */
	public function anchor( $anchor = false )
	{
		if( $anchor === false ) return $this->url['fragment'];
		$this->url['fragment'] = $anchor;
		return $this;
	}
	
	/**
	 * Sets or returns the protocol for the URL (http/ftp/etc.).
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.example.com');
	 * $url->protocol('ftp');
	 * echo $url->make();
	 * 
	 * Outputs...
	 * 
	 * 		ftp://www.example.com
	 *  
	 * @param	string	$protocol
	 * @return	mixed
	 */
	public function protocol( $protocol = false )
	{
		if( $protocol === false ) return $this->url['scheme'];
		$this->url['scheme'] = $protocol;
		return $this;
	}
	
	/**
	 * Sets or returns the host (domain) for the url.
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.example.com/stuff?val1=foo');
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
	public function host( $host = false )
	{
		if( $host === false ) return $this->url['host'];
		$this->url['host'] = $host;
		return $this;
	}

	/**
	 * Sets or returns the port for the url.
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.example.com/stuff?val1=foo');
	 * $url->port(8080);
	 * echo $url->make();
	 * 
	 * Outputs...
	 * 
	 * 		http://www.example.com:8080/stuff?val1=foo
	 *  
	 * @param	string	$port
	 * @return	mixed
	 */
	public function port( $port = false )
	{
		if( $port === false ) return $this->url['port'];
		$this->url['port'] = $port;
		return $this;
	}
	
	/**
	 * Sets or returns the user for auth.
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.example.com/stuff?val1=foo');
	 * $url->user('joe');
	 * echo $url->make();
	 * 
	 * Outputs...
	 * 
	 * 		http://joe@www.example.com/stuff?val1=foo
	 *  
	 * @param	string	$user
	 * @return	mixed
	 */
	public function user( $user = false )
	{
		if( $user === false ) return $this->url['user'];
		$this->url['user'] = $user;
		return $this;
	}
	
	/**
	 * Sets or returns the password for auth. User must be specified first.
	 * 
	 * eg...
	 * 
	 * $url = new URLParser\URL('http://www.example.com/stuff?val1=foo');
	 * $url->user('joe');
	 * $url->pass('foobar');
	 * echo $url->make();
	 * 
	 * Outputs...
	 * 
	 * 		http://joe:foobar@www.example.com/stuff?val1=foo
	 *  
	 * @param	string	$pass
	 * @return	mixed
	 */
	public function pass( $pass = false )
	{
		if( $pass === false ) return $this->url['pass'];
		$this->url['pass'] = $pass;
		return $this;
	}
	


	/*
	 * Compiles and returns the URL as a string
	 */
	public function make()
	{
		
		$q = $this->query();
		$s = $this->segments();
		
		$newURL = 
			$this->url['scheme'] . "://" .
			$this->url['user'] .
			( ( $this->url['pass'] == "" ) ? "" : ":" . $this->url['pass'] ) .
			( ( $this->url['user'] == "" ) ? "" : "@" ) .
			$this->url['host'] .
			( ( $this->url['port'] == false ) ? '' : ":" . $this->url['port'] ) .
			$s .
			( ( strlen( $q ) > 0 ) ? "?" . $q : "" ) .
			( ( strlen( $this->url['fragment'] ) > 0 ) ? "#" . $this->url['fragment'] : "" );

		return $newURL;
	}
	
	// Enables static methods so you can do stuff like...
	//
	//  $url = URLParser::addQuery( array('userid',12) )->to('http://www.google.ca/?q=stuff');
	//
	public static function __callStatic( $method, $arguments )
	{
		$class = get_called_class();
		// Create a new instance (will reset $url)
		$instance = new $class;
		return call_user_func_array( array( $instance, $method ) , $arguments );
	}

}
<?php

class UrlTest extends PHPUnit_Framework_TestCase {

	protected $url = false;

	protected function setUp()
	{
		$_SERVER['REQUEST_URI'] = 'products/services/technical/programming';
		$_SERVER["SERVER_PROTOCOL"] = "http://";
		$_SERVER["SERVER_PORT"] = "80";
		$_SERVER['HTTP_HOST'] = "www.example.com";
		$this->url = new codenamegary\URLParser\URL;
	}
	
	public function testConstructor()
	{
		$this->assertInstanceOf( 'codenamegary\\URLParser\\URL', new codenamegary\URLParser\URL );
		
		$url = new codenamegary\URLParser\URL( 'http://www.acme.co/joes/java/hut' );
		$this->assertInstanceOf( 'codenamegary\\URLParser\\URL', $url );
		$this->assertEquals( 'http://www.acme.co/joes/java/hut', $url->make() );

		$url = new codenamegary\URLParser\URL( 'https://www.acme.co/joes/java/hut' );
		$this->assertInstanceOf( 'codenamegary\\URLParser\\URL', $url );
		$this->assertEquals( 'https://www.acme.co/joes/java/hut', $url->make() );

		$url = new codenamegary\URLParser\URL( 'http://www.acme.co:8080/joes/java/hut' );
		$this->assertInstanceOf( 'codenamegary\\URLParser\\URL', $url );
		$this->assertEquals( 'http://www.acme.co:8080/joes/java/hut', $url->make() );

		$url = new codenamegary\URLParser\URL( 'ftps://www.acme.co:8080/joes/java/hut' );
		$this->assertInstanceOf( 'codenamegary\\URLParser\\URL', $url );
		$this->assertEquals( 'ftps://www.acme.co:8080/joes/java/hut', $url->make() );
		
		$this->setExpectedException( 'Exception' );
		$url = new codenamegary\URLParser\URL( 'http:///example.com' );
	}

	public function testTo()
	{
		$this->url->to( 'http://www.foo.com/bar?stuff=thing#place' );
		$this->assertEquals( 'http://www.foo.com/bar?stuff=thing#place', $this->url->make() );
		$this->url->to( 'http://www.foo.com/barr' );
		$this->assertEquals( 'http://www.foo.com/barr', $this->url->make() );
		$this->url->to( 'https://www.foo.com/barr' );
		$this->assertEquals( 'https://www.foo.com/barr', $this->url->make() );
	}

	public function testProtocol()
	{
		$this->url->to( 'http://www.foo.com/bar?stuff=thing#place' );
		$this->url->protocol( 'ftps' );
		$this->assertEquals( 'ftps://www.foo.com/bar?stuff=thing#place', $this->url->make() );
		$this->url->protocol( 'ssh' );
		$this->assertEquals( 'ssh://www.foo.com/bar?stuff=thing#place', $this->url->make() );
	}

	public function testQueryArray()
	{
		$this->url->to( 'http://www.foo.com/bar?stuff=thing#place' );
		$actualQueries = $this->url->queryArray();
		$expectedQueries = array(
			'stuff'		=> 'thing',
		);
		$this->assertEquals( $expectedQueries, $actualQueries );
	}

	public function testQuery()
	{
		$this->url->to( 'http://www.foo.com/bar?stuff=thing#place' );
		$actualStuff = $this->url->query( 'stuff' );
		$expectedStuff = 'thing';
		$this->assertEquals( $expectedStuff, $actualStuff );
	}

	public function testAddQuery()
	{
		$this->url->to( 'http://www.foo.com/bar?stuff=thing#place' );
		$this->url->addQuery( array( 'blarg' => 'snoggle' ) );
		$this->assertEquals( $this->url->query( 'blarg' ), 'snoggle' );
		$this->assertEquals( $this->url->make(), 'http://www.foo.com/bar?stuff=thing&blarg=snoggle#place' );
	}
	
	public function testStripQuery()
	{
		$this->url->to( 'http://www.foo.com/bar?stuff=thing&foo=bar&blarg=snoggle#place' );
		$this->url->stripQuery( 'foo' );
		$this->assertEquals( $this->url->make(), 'http://www.foo.com/bar?stuff=thing&blarg=snoggle#place' );
	}
	
	public function testStripQueries()
	{
		$this->url->to( 'http://www.foo.com/bar?stuff=thing&foo=bar&blarg=snoggle#place' );
		$this->url->stripQueries();
		$this->assertEquals( $this->url->make(), 'http://www.foo.com/bar#place' );
	}

	public function testSwapQuery()
	{
		$this->url->to( 'http://www.foo.com/bar?stuff=thing&foo=bar&blarg=snoggle#place' );
		$this->url->swapQuery( 'foo', 'new' );
		$this->assertEquals( 'http://www.foo.com/bar?stuff=thing&new=bar&blarg=snoggle#place', $this->url->make() );
		$this->url->swapQuery( 'blarg', 'SmarF' );
		$this->assertEquals( 'http://www.foo.com/bar?stuff=thing&new=bar&SmarF=snoggle#place', $this->url->make() );
	}

	public function testSwapQueries()
	{
		$this->url->to( 'http://www.foo.com/bar?stuff=thing&foo=bar&blarg=snoggle#place' );
		$swaps = array(
			'foo'	=> 'goo',
			'blarg'	=> 'yorg',
		);
		$this->url->swapQueries( $swaps );
		$this->assertEquals( 'http://www.foo.com/bar?stuff=thing&goo=bar&yorg=snoggle#place', $this->url->make() );
	}

	public function testSegmentArray()
	{
		$this->url->to( 'http://www.foo.com/bar/foo/acme?stuff=thing&foo=bar&blarg=snoggle#place' );
		$actualSegments = $this->url->segmentArray();
		$expectedSegments = array( 'bar', 'foo', 'acme' );
		$this->assertEquals( $expectedSegments, $actualSegments );
	}
	
	public function testSegments()
	{
		$this->url->to( 'http://www.foo.com/bar/foo/acme?stuff=thing&foo=bar&blarg=snoggle#place' );
		$this->assertTrue( $this->url->segments( 'bar' ) );
		$this->assertTrue( $this->url->segments( 'foo' ) );
		$this->assertTrue( $this->url->segments( 'acme' ) );
		$this->assertFalse( $this->url->segments( 'snoggle' ) );
		$this->assertEquals( '/bar/foo/acme', $this->url->segments() );
	}

	public function testAppendSegment()
	{
		$this->url->to( 'http://www.foo.com/bar/foo/acme?stuff=thing&foo=bar&blarg=snoggle#place' );
		$this->url->appendSegment( 'snoggle' );
		$this->assertEquals( 'http://www.foo.com/bar/foo/acme/snoggle?stuff=thing&foo=bar&blarg=snoggle#place', $this->url->make() );
		$this->url->appendSegment( 'blarg', 'foo' );
		$this->assertEquals( 'http://www.foo.com/bar/foo/blarg/acme/snoggle?stuff=thing&foo=bar&blarg=snoggle#place', $this->url->make() );
		$this->url->appendSegment( 'crikey', 'foo' );
		$this->assertEquals( 'http://www.foo.com/bar/foo/crikey/blarg/acme/snoggle?stuff=thing&foo=bar&blarg=snoggle#place', $this->url->make() );
	}

	public function testPrependSegment()
	{
		$this->url->to( 'http://www.foo.com/bar/foo/acme?stuff=thing&foo=bar&blarg=snoggle#place' );
		$this->url->prependSegment( 'snoggle' );
		$this->assertEquals( 'http://www.foo.com/snoggle/bar/foo/acme?stuff=thing&foo=bar&blarg=snoggle#place', $this->url->make() );
		$this->url->prependSegment( 'blarg', 'foo' );
		$this->assertEquals( 'http://www.foo.com/snoggle/bar/blarg/foo/acme?stuff=thing&foo=bar&blarg=snoggle#place', $this->url->make() );
		$this->url->prependSegment( 'crikey', 'foo' );
		$this->assertEquals( 'http://www.foo.com/snoggle/bar/blarg/crikey/foo/acme?stuff=thing&foo=bar&blarg=snoggle#place', $this->url->make() );
	}
	
	public function testStripSegment()
	{
		$this->url->to( 'http://www.foo.com/bar/foo/acme?stuff=thing&foo=bar&blarg=snoggle#place' );
		$this->url->stripSegment( 'foo' );
		$this->assertEquals( 'http://www.foo.com/bar/acme?stuff=thing&foo=bar&blarg=snoggle#place', $this->url->make() );
		$this->url->stripSegment( 'bar' );
		$this->assertEquals( 'http://www.foo.com/acme?stuff=thing&foo=bar&blarg=snoggle#place', $this->url->make() );
		$this->url->stripSegment( 'acme' );
		$this->assertEquals( 'http://www.foo.com/?stuff=thing&foo=bar&blarg=snoggle#place', $this->url->make() );
	}

	public function testStripSegments()
	{
		$this->url->to( 'http://www.foo.com/bar/foo/acme?stuff=thing&foo=bar&blarg=snoggle#place' );
		$this->url->stripSegments();
		$this->assertEquals( 'http://www.foo.com/?stuff=thing&foo=bar&blarg=snoggle#place', $this->url->make() );
	}

	public function testSwapSegments()
	{
		$this->url->to( 'http://www.foo.com/bar/foo/acme?stuff=thing&foo=bar&blarg=snoggle#place' );
		$this->url->swapSegments(array(
			'bar'	=> 'high',
			'foo'	=> 'cat',
		));
		$this->assertEquals( 'http://www.foo.com/high/cat/acme?stuff=thing&foo=bar&blarg=snoggle#place', $this->url->make() );
	}

	public function testAnchor()
	{
		$this->url->to( 'http://www.foo.com/bar/foo/acme?stuff=thing&foo=bar&blarg=snoggle#place' );
		$this->assertEquals( 'place', $this->url->anchor() );
		$this->url->anchor( 'time' );
		$this->assertEquals( 'time', $this->url->anchor() );
		$this->assertEquals( 'http://www.foo.com/bar/foo/acme?stuff=thing&foo=bar&blarg=snoggle#time', $this->url->make() );
		$this->url->anchor( '' );
		$this->assertEquals( '', $this->url->anchor() );
		$this->assertEquals( 'http://www.foo.com/bar/foo/acme?stuff=thing&foo=bar&blarg=snoggle', $this->url->make() );
	}

	public function testPort()
	{
		$this->url->to( 'http://www.foo.com/bar/foo/acme?stuff=thing&foo=bar&blarg=snoggle#place' );
		$this->assertFalse( $this->url->port() );
		$this->url->to( 'http://www.foo.com:8080/bar/foo/acme?stuff=thing&foo=bar&blarg=snoggle#place' );
		$this->assertEquals( 8080, $this->url->port() );
		$this->url->to( 'https://www.foo.com/bar/foo/acme?stuff=thing&foo=bar&blarg=snoggle#place' );
		$this->assertEquals( false, $this->url->port() );
	}

	public function testUserAndPass()
	{
		$this->url->to( 'http://www.foo.com/bar/foo/acme?stuff=thing&foo=bar&blarg=snoggle#place' );
		$this->assertEquals( '', $this->url->user() );
		$this->url->to( 'http://joe@www.foo.com/bar/foo/acme?stuff=thing&foo=bar&blarg=snoggle#place' );
		$this->assertEquals( 'joe', $this->url->user() );
		$this->url->to( 'http://www.foo.com/bar/foo/acme?stuff=thing&foo=bar&blarg=snoggle#place' );
		$this->url->user( 'jeffery' );
		$this->assertEquals( 'jeffery', $this->url->user() );
		$this->assertEquals( 'http://jeffery@www.foo.com/bar/foo/acme?stuff=thing&foo=bar&blarg=snoggle#place', $this->url->make() );
		$this->url->pass( 'blarney' );
		$this->assertEquals( 'blarney', $this->url->pass() );
		$this->assertEquals( 'http://jeffery:blarney@www.foo.com/bar/foo/acme?stuff=thing&foo=bar&blarg=snoggle#place', $this->url->make() );
	}

	public function testPass()
	{
		$this->url->to( 'http://www.foo.com/bar/foo/acme?stuff=thing&foo=bar&blarg=snoggle#place' );
		$this->assertEquals( '', $this->url->pass() );
		$this->url->to( 'http://joe:blimey@www.foo.com/bar/foo/acme?stuff=thing&foo=bar&blarg=snoggle#place' );
		$this->assertEquals( 'blimey', $this->url->pass() );
		$this->url->to( 'http://www.foo.com/bar/foo/acme?stuff=thing&foo=bar&blarg=snoggle#place' );
		$this->url->pass( 'blarney' );
		$this->assertEquals( 'blarney', $this->url->pass() );
		$this->url->user( 'jack' );
		$this->assertEquals( 'http://jack:blarney@www.foo.com/bar/foo/acme?stuff=thing&foo=bar&blarg=snoggle#place', $this->url->make() );
	}

}

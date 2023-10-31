<?php

namespace Corpus\HttpMessageUtils\Authorization;

use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

class AuthorizationHeaderParserTest extends TestCase {

	/**
	 * @dataProvider authorizationProvider
	 */
	public function testParseString(
		string $header,
		?string $type,
		?string $credentials = null
	) : void {
		$parser = new AuthorizationHeaderParser;
		$result = $parser->parseString($header);

		if( $type === null ) {
			$this->assertNull($result);

			return;
		}

		$this->assertNotNull($result);

		$this->assertSame($type, $result->getType());
		$this->assertSame($credentials, $result->getCredentials());
	}

	/**
	 * @dataProvider authorizationProvider
	 */
	public function testParseServerRequest(
		string $header,
		?string $type,
		?string $credentials = null
	) : void {
		$parser  = new AuthorizationHeaderParser;
		$request = new ServerRequest("POST", "https://example.com", [
			'Authorization' => $header,
		]);

		$result = $parser->parseServerRequest($request);

		if( $type === null ) {
			$this->assertNull($result);

			return;
		}

		$this->assertNotNull($result);

		$this->assertSame($type, $result->getType());
		$this->assertSame($credentials, $result->getCredentials());
	}

	public function testParseServerRequest_noHeader() : void {
		$parser  = new AuthorizationHeaderParser;
		$request = new ServerRequest("GET", "https://example.com");

		$result = $parser->parseServerRequest($request);
		$this->assertNull($result);
	}

	public function authorizationProvider() : \Generator {
		yield [ '', null ];
		yield [ 'Foo', null ];
		yield [ 'Foo bar', 'Foo', 'bar' ];
		yield [ '   Foo    bar   ', 'Foo', 'bar' ];
		yield [ 'Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==', 'Basic', 'QWxhZGRpbjpvcGVuIHNlc2FtZQ==' ];
		yield [ 'QueryAuth foo=bar baz=qux', 'QueryAuth', 'foo=bar baz=qux' ];
		yield [ '	OtherAuth			foo=bar    baz=qux	', 'OtherAuth', 'foo=bar    baz=qux' ];
	}

}

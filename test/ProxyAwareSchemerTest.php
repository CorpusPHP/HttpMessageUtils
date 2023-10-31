<?php

namespace Corpus\HttpMessageUtils;

use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

class ProxyAwareSchemerTest extends TestCase {

	/**
	 * @backupGlobals
	 * @dataProvider positiveServerKeyProvider
	 */
	public function test_withDetectedScheme_https( string $key, string $value ) : void {
		$request = new ServerRequest('GET', 'http://localhost');

		$server                = [ $key => $value ];
		$schemerManuallyPassed = new ProxyAwareSchemer($server);
		$this->assertSame('https', $schemerManuallyPassed->withUriWithDetectedScheme($request)->getUri()->getScheme());

		$_SERVER     = [ $key => $value ];
		$schemerPost = new ProxyAwareSchemer;

		$this->assertSame('https', $schemerPost->withUriWithDetectedScheme($request)->getUri()->getScheme());
	}

	public function test_withDetectedScheme_http() : void {
		$request = new ServerRequest('GET', 'http://localhost');

		$server                = [];
		$schemerManuallyPassed = new ProxyAwareSchemer($server);
		$this->assertSame('http', $schemerManuallyPassed->withUriWithDetectedScheme($request)->getUri()->getScheme());

		$_SERVER     = [];
		$schemerPost = new ProxyAwareSchemer;

		$this->assertSame('http', $schemerPost->withUriWithDetectedScheme($request)->getUri()->getScheme());
	}

	public function positiveServerKeyProvider() : \Generator {
		foreach( ProxyAwareSchemer::HTTPS_EXPECTED_SERVER_VALUES as $key => $val ) {
			yield [ $key, $val ];
		}
	}

	/**
	 * @dataProvider linkServerPortProvider
	 * @param array<string,scalar> $server
	 */
	public function test_forwarded_port(
		string $expected,
		string $link,
		array $server,
		bool $detectPort = true,
		int $default = ProxyAwareSchemer::REMOVE_PORT
	) : void {
		$request = new ServerRequest('GET', $link);

		$schemer = new ProxyAwareSchemer($server);

		$this->assertSame($expected, $schemer->withUriWithDetectedScheme($request, $detectPort, $default)->getUri()->__toString());
	}

	public function linkServerPortProvider() : \Generator {
		yield 'protocol should stay the same 1' => [ 'https://test.example.com:8080/foo', 'https://test.example.com/foo', [ 'HTTP_X_FORWARDED_PORT' => 8080 ] ];
		yield 'protocol should stay the same 2' => [ 'http://test.example.com:8080/foo', 'http://test.example.com/foo', [ 'HTTP_X_FORWARDED_PORT' => 8080 ] ];

		yield 'protocol should change and new port should be respected' => [ 'https://test.example.com:8080/foo', 'http://test.example.com/foo', [ 'HTTP_X_FORWARDED_PORT' => 8080, 'HTTP_X_FORWARDED_PROTOCOL' => 'https' ] ];

		yield 'change in protocol should remove port' => [ 'https://test.example.com/foo', 'http://test.example.com/foo', [ 'HTTP_X_FORWARDED_PROTOCOL' => 'https' ] ];

		yield 'Default port should overwrite when not found' => [ 'https://test.example.com:9090/foo', 'http://test.example.com/foo', [ 'HTTP_X_FORWARDED_PROTOCOL' => 'https' ], true, 9090 ];
		yield 'Default port should not overwrite when detection disabled' => [ 'https://test.example.com:2020/foo', 'http://test.example.com:2020/foo', [ 'HTTP_X_FORWARDED_PORT' => 8080, 'HTTP_X_FORWARDED_PROTOCOL' => 'https' ], false ];
	}

}

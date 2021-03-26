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

		$server                = [$key => $value];
		$schemerManuallyPassed = new ProxyAwareSchemer(null, $server);
		$this->assertSame('https', $schemerManuallyPassed->withUriWithDetectedScheme($request)->getUri()->getScheme());

		$schemerPre  = new ProxyAwareSchemer;
		$_SERVER     = [ $key => $value ];
		$schemerPost = new ProxyAwareSchemer;


		$this->assertSame('https', $schemerPre->withUriWithDetectedScheme($request)->getUri()->getScheme());
		$this->assertSame('https', $schemerPost->withUriWithDetectedScheme($request)->getUri()->getScheme());
	}

	public function test_withDetectedScheme_http() : void {
		$request = new ServerRequest('GET', 'http://localhost');

		$server                = [];
		$schemerManuallyPassed = new ProxyAwareSchemer(null, $server);
		$this->assertSame('http', $schemerManuallyPassed->withUriWithDetectedScheme($request)->getUri()->getScheme());

		$schemerPre  = new ProxyAwareSchemer;
		$_SERVER     = [];
		$schemerPost = new ProxyAwareSchemer;


		$this->assertSame('http', $schemerPre->withUriWithDetectedScheme($request)->getUri()->getScheme());
		$this->assertSame('http', $schemerPost->withUriWithDetectedScheme($request)->getUri()->getScheme());
	}

	public function positiveServerKeyProvider() : \Generator {
		foreach( ProxyAwareSchemer::HTTPS_EXPECTED_SERVER_VALUES as $key => $val ) {
			yield [ $key, $val ];
		}
	}

}

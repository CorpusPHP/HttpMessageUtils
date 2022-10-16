<?php

namespace Cookie;

use Corpus\HttpMessageUtils\Cookie\CookieBuilder;
use PHPUnit\Framework\TestCase;

class CookieBuilderTest extends TestCase {

	public function testGettersAndSetters() : void {
		$cookieBuilder = new CookieBuilder('ExampleCookie');

		$valueCheck = $cookieBuilder->withName('WayDifferentCookie');
		$this->assertSame('WayDifferentCookie', $valueCheck->getName());
		$this->assertSame('ExampleCookie', $cookieBuilder->getName());

		$valueCheck = $cookieBuilder->withValue('test');
		$this->assertSame('test', $valueCheck->getValue());
		$this->assertSame('', $cookieBuilder->getValue());

		$valueCheck = $cookieBuilder->withExpiration(123);
		$this->assertSame(123, $valueCheck->getExpiration());
		$this->assertSame(0, $cookieBuilder->getExpiration());

		$valueCheck = $cookieBuilder->withPath('/test');
		$this->assertSame('/test', $valueCheck->getPath());
		$this->assertSame('', $cookieBuilder->getPath());

		$valueCheck = $cookieBuilder->withDomain('www.example.dev');
		$this->assertSame('www.example.dev', $valueCheck->getDomain());
		$this->assertSame('', $cookieBuilder->getDomain());

		$valueCheck = $cookieBuilder->withHttpOnly(true);
		$this->assertTrue($valueCheck->isHttpOnly());
		$this->assertFalse($cookieBuilder->isHttpOnly());

		$valueCheck = $cookieBuilder->withSecure(true);
		$this->assertTrue($valueCheck->isSecure());
		$this->assertFalse($cookieBuilder->isSecure());

		$valueCheck = $cookieBuilder->withSameSite('Lax');
		$this->assertSame('Lax', $valueCheck->getSameSite());
		$this->assertSame('', $cookieBuilder->getSameSite());
	}

	public function test_withExpireNow() : void {
		$cookieBuilder = (new CookieBuilder('ExampleCookie'))
			->withValue('bye bye birdie')
			->withExpiration(123);

		$cookieBuilder = $cookieBuilder->withExpireNow();
		$this->assertSame('', $cookieBuilder->getValue());
		$this->assertLessThan(0, $cookieBuilder->getExpiration());
	}

}

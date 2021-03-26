<?php

namespace Corpus\HttpMessageUtils;

use GuzzleHttp\Psr7\Response;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;

class ResponseSenderTest extends TestCase {

	use PHPMock;

	/**
	 * @dataProvider sendOptionProvider
	 */
	public function testSend( bool $fullStmt ) : void {
		$response = new Response(
			$status = 200,
			$headers = [
				'Content-Type' => 'text/plain',
			],
			$body = uniqid(true, true),
			$version = '1.1'
		);

		$sent_headers = [];
		$header       = $this->getFunctionMock(__NAMESPACE__, 'header');

		$header->expects($this->any())->willReturnCallback(function ( $header, $replace ) use ( &$sent_headers ) {
			return $sent_headers[] = $header;
		});

		$sender = new ResponseSender($fullStmt);

		ob_start();
		$sender->send($response);
		$output = ob_get_clean();

		if( $fullStmt ) {
			$this->assertSame([
				"HTTP/$version $status OK",
				"Content-Type: text/plain",
			], $sent_headers);
		} else {
			$this->assertSame([
				"Content-Type: text/plain",
			], $sent_headers);
		}

		$this->assertSame($body, $output);
	}

	public function sendOptionProvider() : \Generator {
		yield [ true ];

		yield [ false ];
	}

}

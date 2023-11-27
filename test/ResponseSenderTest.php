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
			$body = uniqid('test_', true),
			$version = '1.1'
		);

		$response = $response->withHeader('X-Test', 'test')
			->withAddedHeader('X-Test', 'test2')
			->withAddedHeader('X-Test', 'test3');

		$sent_headers = [];
		$header       = $this->getFunctionMock(__NAMESPACE__, 'header');

		$header->expects($this->any())
			->willReturnCallback(function ( $header, $replace ) use ( &$sent_headers ) {
				return $sent_headers[] = [ $header, $replace ];
			});

		if( !$fullStmt ) {
			$http_response_code = $this->getFunctionMock(__NAMESPACE__, 'http_response_code');
			$http_response_code
				->expects($this->once())
				->with($status);
		}

		$sender = new ResponseSender($fullStmt);

		ob_start();
		$sender->send($response);
		$output = ob_get_clean();

		$expectedHeaders = [
			[ 'Content-Type: text/plain', true ],
			[ 'X-Test: test', true ],
			[ 'X-Test: test2', false ],
			[ 'X-Test: test3', false ],
		];

		if( $fullStmt ) {
			array_unshift($expectedHeaders, [ "HTTP/$version $status OK", true ]);
		}

		$this->assertSame($expectedHeaders, $sent_headers);

		$this->assertSame($body, $output);
	}

	public function sendOptionProvider() : \Generator {
		yield [ true ];

		yield [ false ];
	}

}

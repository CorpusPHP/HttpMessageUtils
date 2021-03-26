<?php

namespace Corpus\HttpMessageUtils;

use Psr\Http\Message\ResponseInterface;

/**
 * Utility to actualize a PSR7 ResponseInterface
 *
 * Sends headers and body.
 *
 * Inspired by `http-interop/response-sender` MIT License Copyright (c) 2017 Woody Gilk
 *
 * @see https://github.com/http-interop/response-sender/
 */
class ResponseSender {

	/** @var bool */
	private $fullHttpStmtHeader;

	/** @var bool */
	private $rewindBody;

	/**
	 * ResponseSender constructor.
	 *
	 * @param bool $fullHttpStmtHeader Setting to `true` enables full HTTP statement construction which allows
	 *                                 non-standard reason phrases and potentially mismatched protocol versions.
	 *                                 Use with care.
	 * @param bool $rewindBody         Setting to `false` allows you to disable rewinding the body of the response
	 *                                 before transmission.
	 */
	public function __construct(
		bool $fullHttpStmtHeader = false,
		bool $rewindBody = true
	) {
		$this->fullHttpStmtHeader = $fullHttpStmtHeader;
		$this->rewindBody         = $rewindBody;
	}

	public function send( ResponseInterface $response ) : void {
		if( $this->fullHttpStmtHeader ) {
			$httpStmt = sprintf('HTTP/%s %s %s',
				$response->getProtocolVersion(),
				$response->getStatusCode(),
				$response->getReasonPhrase()
			);

			header($httpStmt, true, $response->getStatusCode());
		} else {
			http_response_code($response->getStatusCode());
		}

		foreach( $response->getHeaders() as $name => $values ) {
			foreach( $values as $value ) {
				header(
					sprintf("%s: %s", $name, $value),
					false
				);
			}
		}

		$stream = $response->getBody();
		if( $this->rewindBody && $stream->isSeekable() ) {
			$stream->rewind();
		}

		while( !$stream->eof() ) {
			echo $stream->read(1024 * 8);
		}
	}

}

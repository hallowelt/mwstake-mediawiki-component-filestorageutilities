<?php

namespace MWStake\MediaWiki\Component\FileStorageUtilities;

use Wikimedia\FileBackend\FileBackend;

class StorageHelper {

	/**
	 * @param FileBackend $fileBackend
	 */
	public function __construct( private readonly FileBackend $fileBackend ) {
	}

	/**
	 * @param string $path
	 * @param string $filename
	 * @param string $container
	 * @return string
	 */
	public function compileZonePath(
		string $path = '', string $filename = '', string $container = 'wiki_data'
	): string {
		$filename = trim( $filename, '/' );
		$path = trim( $path, '/' );
		$backendName = $this->fileBackend->getName();
		if ( $path === '' && $filename === '' ) {
			return "mwstore://$backendName/$container";
		} elseif ( $path === '' ) {
			return "mwstore://$backendName/$container/$filename";
		} elseif ( $filename === '' ) {
			return "mwstore://$backendName/$container/$path";
		}
		return "mwstore://$backendName/$container/$path/$filename";
	}
}

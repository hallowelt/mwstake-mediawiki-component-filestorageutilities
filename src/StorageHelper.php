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
	 * @return string
	 */
	public function compileZonePath( string $path = '', string $filename = '' ): string {
		$filename = trim( $filename, '/' );
		$path = trim( $path, '/' );
		$backendName = $this->fileBackend->getName();
		if ( $path === '' && $filename === '' ) {
			return "mwstore://$backendName/bluespice";
		} elseif ( $path === '' ) {
			return "mwstore://$backendName/bluespice/$filename";
		} elseif ( $filename === '' ) {
			return "mwstore://$backendName/bluespice/$path";
		}
		return "mwstore://$backendName/bluespice/$path/$filename";
	}
}

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

	/**
	 * @param string $root
	 * @param string $file
	 * @return string
	 */
	public function makeInstancePath( string $root, string $file = '' ): string {
		$backendName = $this->fileBackend->getName();
		$root = trim( $root, '/' );
		if ( !$file ) {
			return "mwstore://$backendName/instances-public/$root";
		}
		return "mwstore://$backendName/instances-public/$root/" . trim( $file, '/' );
	}

	/**
	 * @param string $file
	 * @return string
	 */
	public function makeArchiveInstancePath( string $file ): string {
		$backendName = $this->fileBackend->getName();
		$file = trim( $file, '/' );
		if ( !$file ) {
			return "mwstore://$backendName/archive-public";
		}
		return "mwstore://$backendName/archive-public/$file";
	}
}

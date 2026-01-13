<?php

namespace MWStake\MediaWiki\Component\FileStorageUtilities;

class TempFSFileBackend extends \FSFileBackend {
	public function getTempFilePath( string $filename, string $path, bool $prepareDir = true ) {
		$helper = new StorageHelper( $this );
		if ( $prepareDir ) {
			$dirPath = $helper->compileZonePath( $path, '' );
			$this->prepare( [ 'dir' => $dirPath ] );
		}
		$storagePath = $helper->compileZonePath( $path, $filename );

		return $this->resolveToFSPath( $storagePath );
	}
}
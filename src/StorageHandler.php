<?php

namespace MWStake\MediaWiki\Component\FileStorageUtilities;

use FSFile;
use Wikimedia\FileBackend\FileBackend;
use Wikimedia\FileBackend\FSFile\TempFSFile;

class StorageHandler {

	/**
	 * @param FileBackend $fileBackend
	 * @param FileBackend $tempBackend
	 * @param FileBackend|null $globalBackend
	 */
	public function __construct(
		private readonly FileBackend $fileBackend,
		private readonly FileBackend $tempBackend,
		private readonly ?FileBackend $globalBackend = null
	) {
	}

	/**
	 * Get a file from the temp backend
	 *
	 * @param string $filename
	 * @param string $path
	 * @return TempFSFile|null
	 */
	public function getTempFile( string $filename, string $path = '' ): ?StoredFile {
		return $this->doGetStoredFile( $this->tempBackend, $filename, $path );
	}

	/**
	 * Get physical temp file path - for passing to functions that want to write to it
	 *
	 * @param string $filename
	 * @param string $path
	 * @param bool $prepareDir
	 * @return string
	 */
	public function getTempFilePath( string $filename, string $path = '', bool $prepareDir = true ): string {
		return $this->tempBackend->getTempFilePath( $filename, $path, $prepareDir );
	}

	/**
	 * @param string $filename
	 * @param string $path
	 * @return TempFSFile|null
	 */
	public function getFile( string $filename, string $path = '' ): ?StoredFile {
		return $this->doGetStoredFile( $this->fileBackend, $filename, $path );
	}

	/**
	 * @param bool $useTempBackend
	 * @return StorageTransaction
	 */
	public function newTransaction( bool $useTempBackend = false ): StorageTransaction {
		return new StorageTransaction( $useTempBackend ? $this->tempBackend : $this->fileBackend );
	}

	/**
	 * @return GlobalStorageTransaction
	 */
	public function newGlobalTransaction(): GlobalStorageTransaction {
		if ( !$this->globalBackend ) {
			throw new \RuntimeException( 'Global backend not configured' );
		}
		return new GlobalStorageTransaction( $this->globalBackend );
	}

	/**
	 * @param string $type 'main':default|'temp'
	 * @return FileBackend
	 */
	public function getBackend( string $type = 'main' ): FileBackend {
		return $type === 'temp' ? $this->tempBackend : $this->fileBackend;
	}

	/**
	 * @param FileBackend $backend
	 * @param string $filename
	 * @param string $path
	 * @return FSFile|null
	 */
	private function doGetStoredFile( FileBackend $backend, string $filename, string $path = '' ): ?StoredFile {
		$helper = new StorageHelper( $backend );
		$tempFile = $backend->getLocalCopy( [ 'src' => $helper->compileZonePath( $path, $filename ) ] );
		if ( !$tempFile ) {
			return null;
		}

		return new StoredFile(
			$tempFile,
			$backend->getName(),
			$filename,
			$path
		);
	}
}
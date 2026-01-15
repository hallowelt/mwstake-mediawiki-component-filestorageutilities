<?php

namespace MWStake\MediaWiki\Component\FileStorageUtilities;

use FSFile;
use Wikimedia\FileBackend\FileBackend;
use Wikimedia\FileBackend\FSFile\TempFSFile;

class StorageHandler {

	public const BACKEND_TYPE_MAIN = 'main';
	public const BACKEND_TYPE_TEMP = 'temp';
	public const BACKEND_TYPE_INSTANCE = 'instance';

	/**
	 * @param \FileBackendGroup $fileBackendGroup
	 * @param string $backendName
	 */
	public function __construct(
		private readonly \FileBackendGroup $fileBackendGroup,
		private readonly string $backendName
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
		return $this->doGetStoredFile( $this->getTempBackend(), $filename, $path );
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
		return $this->getTempBackend()->getTempFilePath( $filename, $path, $prepareDir );
	}

	/**
	 * @param string $filename
	 * @param string $path
	 * @param FileBackend|null $backend
	 * @return TempFSFile|null
	 */
	public function getFile( string $filename, string $path = '', ?FileBackend $backend = null ): ?StoredFile {
		$backend = $backend ?? $this->getMainBackend();
		return $this->doGetStoredFile( $backend, $filename, $path );
	}

	/**
	 * @param bool $useTempBackend
	 * @return StorageTransaction
	 */
	public function newTransaction( bool $useTempBackend = false ): StorageTransaction {
		return new StorageTransaction( $useTempBackend ? $this->getTempBackend() : $this->getMainBackend() );
	}

	/**
	 * @return StorageTransaction
	 */
	public function newTempTransaction(): StorageTransaction {
		return $this->newTransaction( true );
	}

	/**
	 * @return FileBackend
	 */
	public function getMainBackend(): FileBackend {
		return $this->getBackend( $this->backendName );
	}

	/**
	 * @return TempFSFileBackend
	 */
	public function getTempBackend(): TempFSFileBackend {
		return $this->getBackend( 'bluespice-local-backend' );
	}

	/**
	 * @param string $name
	 * @return FileBackend
	 */
	public function getBackend( string $name ): FileBackend {
		return $this->fileBackendGroup->get( $name );
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

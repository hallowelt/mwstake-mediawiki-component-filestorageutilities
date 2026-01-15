<?php

namespace MWStake\MediaWiki\Component\FileStorageUtilities;

class StorageTransaction extends TransactionBase {

	/**
	 * Create file on storage backend
	 *
	 * For details see \Wikimedia\FileBackend\FileBackend::doOperations
	 *
	 * @param string $filename
	 * @param string $content
	 * @param string $path
	 * @param array $opts
	 * @return StorageTransaction
	 */
	public function create(
		string $filename, string $content, string $path = '', array $opts = []
	): StorageTransaction {
		$this->addPrepare( $path );
		$this->operations[] = array_merge( [
			'op' => 'create',
			'dst' => $this->storageHelper->compileZonePath( $path, $filename ),
			'content' => $content,
		], $opts );

		return $this;
	}

	/**
	 * @param string $sourcePath
	 * @param string $filename
	 * @param string $path
	 * @param array $opts
	 * @return $this
	 */
	public function store( string $sourcePath, string $filename, string $path, array $opts = [] ) {
		$this->operations[] = array_merge( [
			'op' => 'store',
			'src' => $sourcePath,
			'dst' => $this->storageHelper->compileZonePath( $path, $filename ),
		], $opts );

		return $this;
	}

	/**
	 * @param string $srcFilename
	 * @param string $srcPath
	 * @param string $dstFilename
	 * @param string $dstPath
	 * @param array $opts
	 * @return $this
	 */
	public function copy(
		string $srcFilename, string $srcPath, string $dstFilename, string $dstPath, array $opts = []
	) {
		$this->addPrepare( $dstPath );
		$this->operations[] = array_merge( [
			'op' => 'copy',
			'src' => $this->storageHelper->compileZonePath( $srcPath, $srcFilename ),
			'dst' => $this->storageHelper->compileZonePath( $dstPath, $dstFilename ),
		], $opts );

		return $this;
	}

	/**
	 * @param string $srcFilename
	 * @param string $srcPath
	 * @param string $dstFilename
	 * @param string $dstPath
	 * @param array $opts
	 * @return $this
	 */
	public function move(
		string $srcFilename, string $srcPath, string $dstFilename, string $dstPath, array $opts = []
	) {
		$this->addPrepare( $dstPath );
		$this->operations[] = array_merge( [
			'op' => 'move',
			'src' => $this->storageHelper->compileZonePath( $srcPath, $srcFilename ),
			'dst' => $this->storageHelper->compileZonePath( $dstPath, $dstFilename ),
		], $opts );

		return $this;
	}

	/**
	 * @param string $filename
	 * @param string $path
	 * @param array $opts
	 * @return StorageTransaction
	 */
	public function delete( string $filename, string $path, array $opts = [] ): StorageTransaction {
		$this->operations[] = array_merge( [
			'op' => 'delete',
			'src' => $this->storageHelper->compileZonePath( $path, $filename ),
		], $opts );

		return $this;
	}

	/**
	 * @param string $path
	 * @param array $opts
	 * @return StorageTransaction
	 */
	public function deleteDirectory( string $path, array $opts = [] ): StorageTransaction {
		$list = $this->fileBackend->getFileList( [
			'dir' => $this->storageHelper->compileZonePath( $path, '' ),
			'topOnly' => false
		] );
		foreach ( $list as $file ) {
			$this->operations[] = array_merge( [
				'op' => 'delete',
				'src' => $this->storageHelper->compileZonePath( $path, $file )
			], $opts );
		}

		return $this;
	}

	/**
	 * See \Wikimedia\FileBackend\FileBackend::doOperations
	 * @param string $name
	 * @param string $value
	 * @return StorageTransaction
	 */
	public function setOption( string $name, string $value ): StorageTransaction {
		$this->options[ $name ] = $value;
		return $this;
	}

	/**
	 * @param string $dstPath
	 * @return void
	 */
	private function addPrepare( string $dstPath ) {
		if ( $dstPath ) {
			$this->operations[] = [
				'op' => 'prepare',
				'dir' => $this->storageHelper->compileZonePath( $dstPath, '' ),
			];
		}
	}
}

<?php

namespace MWStake\MediaWiki\Component\FileStorageUtilities;

class GlobalStorageTransaction extends TransactionBase {

	/**
	 * @param string $op
	 * @param string $src
	 * @param string $dst
	 * @param bool $prepare
	 * @return GlobalStorageTransaction
	 */
	private function globalDirectoryOperation(
		string $op, string $src, string $dst, bool $prepare
	): GlobalStorageTransaction {
		$fl = $this->fileBackend->getFileList( [
			'dir' => $this->storageHelper->makeRootPath( $src ), 'topOnly' => false
		] );
		foreach ( $fl as $fileLocation  ) {
			$bits = explode( '/', $fileLocation );
			array_pop( $bits );
			$path = implode( '/', $bits );
			if ( $path && $prepare ) {
				$this->operations[] = [
					'op' => 'prepare',
					'dir' => $this->storageHelper->makeRootPath( $dst, $path ),
				];
			}
			$opDef = [
				'op' => $op,
				'src' => $this->storageHelper->makeRootPath( $src, $fileLocation ),
			];
			if ( $dst ) {
				$opDef['dst'] = $this->storageHelper->makeRootPath( $dst, $fileLocation );
			}

			$this->operations[] = $opDef;
		}

		return $this;
	}

	/**
	 * @param string $src
	 * @param string $dst
	 * @return GlobalStorageTransaction
	 */
	public function copyDirectory( string $src, string $dst ): GlobalStorageTransaction {
		return $this->globalDirectoryOperation( 'copy', $src, $dst, true );
	}

	/**
	 * @param string $src
	 * @param string $dst
	 * @return GlobalStorageTransaction
	 */
	public function moveDirectory( string $src, string $dst ): GlobalStorageTransaction {
		 $this->globalDirectoryOperation( 'move', $src, $dst, true );
		 $this->addClean( $this->storageHelper->makeRootPath( $src ) );

		 return $this;
	}

	/**
	 * @param string $src
	 * @return GlobalStorageTransaction
	 */
	public function deleteDirectory( string $src ): GlobalStorageTransaction {
		$this->globalDirectoryOperation( 'delete', $src, '', false );
		$this->addClean( $this->storageHelper->makeRootPath( $src ) );
		return $this;
	}
}

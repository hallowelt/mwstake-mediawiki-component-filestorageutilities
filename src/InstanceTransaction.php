<?php

namespace MWStake\MediaWiki\Component\FileStorageUtilities;

class InstanceTransaction extends TransactionBase {

	/**
	 * @param string $op
	 * @param string $src
	 * @param string $dst
	 * @param bool $prepare
	 * @param array $exclude
	 * @return InstanceTransaction
	 */
	private function instanceDirectoryOperation(
		string $op, string $src, string $dst, bool $prepare, array $exclude = []
	): InstanceTransaction {
		$fl = $this->fileBackend->getFileList( [
			'dir' => $this->storageHelper->makeInstancePath( $src ), 'topOnly' => false
		] );
		foreach ( $fl as $fileLocation ) {
			$bits = explode( '/', $fileLocation );
			$filename = array_pop( $bits );
			if ( in_array( $filename, $exclude ) ) {
				continue;
			}
			$path = implode( '/', $bits );
			if ( $path && $prepare ) {
				$this->operations[] = [
					'op' => 'prepare',
					'dir' => $this->storageHelper->makeInstancePath( $dst, $path ),
				];
			}
			$opDef = [
				'op' => $op,
				'src' => $this->storageHelper->makeInstancePath( $src, $fileLocation ),
			];
			if ( $dst ) {
				$opDef['dst'] = $this->storageHelper->makeInstancePath( $dst, $fileLocation );
			}

			$this->operations[] = $opDef;
		}

		return $this;
	}

	/**
	 * @param string $src
	 * @param string $dst
	 * @return InstanceTransaction
	 */
	public function copyInstance( string $src, string $dst ): InstanceTransaction {
		return $this->instanceDirectoryOperation( 'copy', $src, $dst, true, [ '.smw.json' ] );
	}

	/**
	 * @param string $src
	 * @param string $dst
	 * @return InstanceTransaction
	 */
	public function moveInstanceDirectory( string $src, string $dst ): InstanceTransaction {
		 $this->instanceDirectoryOperation( 'move', $src, $dst, true );
		 $this->addClean( $this->storageHelper->makeInstancePath( $src ) );

		 return $this;
	}

	/**
	 * @param string $src
	 * @return InstanceTransaction
	 */
	public function deleteInstanceDirectory( string $src ): InstanceTransaction {
		$this->instanceDirectoryOperation( 'delete', $src, '', false );
		$this->addClean( $this->storageHelper->makeInstancePath( $src ) );
		return $this;
	}

	/**
	 * @param string $sourcePath
	 * @param string $targetFile
	 * @param array $opts
	 * @return $this
	 */
	public function storeToArchive( string $sourcePath, string $targetFile, array $opts = [] ): InstanceTransaction {
		$this->operations[] = array_merge( [
			'op' => 'store',
			'src' => $sourcePath,
			'dst' => $this->storageHelper->makeArchiveInstancePath( $targetFile )
		], $opts );

		return $this;
	}
}

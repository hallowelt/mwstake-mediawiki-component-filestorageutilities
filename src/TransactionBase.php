<?php

namespace MWStake\MediaWiki\Component\FileStorageUtilities;

use StatusValue;
use Wikimedia\FileBackend\FileBackend;

abstract class TransactionBase {

	/** @var array */
	protected array $operations = [];
	/** @var array */
	protected array $options = [];

	protected StorageHelper $storageHelper;

	/**
	 * @param FileBackend $fileBackend
	 */
	public function __construct( protected readonly FileBackend $fileBackend ) {
		$this->storageHelper = new StorageHelper( $fileBackend );
	}

	/**
	 * See \Wikimedia\FileBackend\FileBackend::doOperations
	 * @param string $name
	 * @param string $value
	 * @return TransactionBase
	 */
	public function setOption( string $name, string $value ): TransactionBase {
		$this->options[ $name ] = $value;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getOperations(): array {
		return $this->operations;
	}

	/**
	 * @return array
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * @return StatusValue
	 */
	public function commit(): StatusValue {
		if ( empty( $this->operations ) ) {
			return StatusValue::newGood();
		}
		$operations = [];
		$cleanOps = [];
		$status = StatusValue::newGood();
		foreach ( $this->operations as $operation ) {
			if ( $operation['op'] === 'prepare' ) {
				$status->merge( $this->fileBackend->prepare( $operation ) );
				continue;
			}
			if ( $operation['op'] === 'clean' ) {
				$cleanOps[] = $operation;
				continue;
			}
			$operations[] = $operation;
		}
		if ( !$status->isOK() ) {
			return $status;
		}

		$this->operations = [];
		if ( !empty( $operations ) ) {
			$status = $this->fileBackend->doOperations( $operations );
		}
		if ( !$status->isOK() ) {
			return $status;
		}
		if ( $cleanOps ) {
			foreach ( $cleanOps as $op ) {
				$status->merge( $this->fileBackend->clean( $op ) );
			}
		}
		return $status;
	}

	/**
	 * @param string $location
	 * @return void
	 */
	protected function addClean( string $location ) {
		$this->operations[] = [
			'op' => 'clean',
			'dir' => $location,
			'recursive' => true
		];
	}
}

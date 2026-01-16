<?php

namespace MWStake\MediaWiki\Component\FileStorageUtilities\Tests;

use FileBackendGroup;
use FSFileBackend;
use MWStake\MediaWiki\Component\FileStorageUtilities\StorageHandler;
use MWStake\MediaWiki\Component\FileStorageUtilities\StorageTransaction;
use MWStake\MediaWiki\Component\FileStorageUtilities\TempFSFileBackend;
use PHPUnit\Framework\TestCase;
use Wikimedia\FileBackend\FileBackend;

/**
 * @covers \MWStake\MediaWiki\Component\FileStorageUtilities\StorageHandler
 */
class StorageHandlerTest extends TestCase {

	/**
	 * @covers \MWStake\MediaWiki\Component\FileStorageUtilities\StorageHandler::getMainBackend
	 * @covers \MWStake\MediaWiki\Component\FileStorageUtilities\StorageHandler::getTempBackend
	 * @covers \MWStake\MediaWiki\Component\FileStorageUtilities\StorageHandler::newTransaction
	 * @return void
	 */
	public function testNewTransaction() {
		$mainBackend = $this->createMock( FSFileBackend::class );
		$tempBackend = $this->createMock( TempFSFileBackend::class );

		$backendGroup = $this->createMock( FileBackendGroup::class );
		$backendGroup->method( 'get' )->willReturnCallback(
			static function ( $name ) use ( $mainBackend, $tempBackend ) {
				return match ( $name ) {
					'main-backend' => $mainBackend,
					'data-local-backend' => $tempBackend,
					default => null,
				};
			}
		);

		$handler = new StorageHandler( $backendGroup, 'main-backend' );

		// Retrieve main backend
		$this->assertInstanceOf( FileBackend::class, $handler->getMainBackend() );
		$this->assertNotInstanceOf( TempFSFileBackend::class, $handler->getMainBackend() );

		// Retrieve temp backend
		$this->assertInstanceOf( TempFSFileBackend::class, $handler->getTempBackend() );
		$this->assertInstanceOf( StorageTransaction::class, $handler->newTransaction() );
		$this->assertInstanceOf( StorageTransaction::class, $handler->newTempTransaction() );
	}

	// Other public methods either invoke final methods of backend, or are tested in other tests.
}

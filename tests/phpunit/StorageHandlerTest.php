<?php

namespace MWStake\MediaWiki\Component\FileStorageUtilities\Tests;

use FSFileBackend;
use MWStake\MediaWiki\Component\FileStorageUtilities\InstanceTransaction;
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
	 * @covers \MWStake\MediaWiki\Component\FileStorageUtilities\StorageHandler::getBackend
	 * @covers \MWStake\MediaWiki\Component\FileStorageUtilities\StorageHandler::newTransaction
	 * @return void
	 * @dataProvider provideHandlers
	 */
	public function testNewTransaction( StorageHandler $handler, bool $hasInstance ) {

		// Retrieve main backend
		$this->assertInstanceOf( FileBackend::class, $handler->getBackend() );
		$this->assertNotInstanceOf( TempFSFileBackend::class, $handler->getBackend() );

		// Retrieve temp backend
		$this->assertInstanceOf( TempFSFileBackend::class, $handler->getBackend( StorageHandler::BACKEND_TYPE_TEMP ) );

		$this->assertInstanceOf( StorageTransaction::class, $handler->newTransaction() );
		$this->assertInstanceOf( StorageTransaction::class, $handler->newTempTransaction() );

		if ( !$hasInstance ) {
			// Attempt to retrieve instance backend - not set, should throw exception
			$this->expectException( \Exception::class );
			$handler->getBackend( StorageHandler::BACKEND_TYPE_INSTANCE );

			$this->expectException( \Exception::class );
			$handler->newInstanceTransaction();
		} else {
			$this->assertInstanceOf(
				FileBackend::class, $handler->getBackend( StorageHandler::BACKEND_TYPE_INSTANCE )
			);
			$this->assertNotInstanceOf(
				FSFileBackend::class, $handler->getBackend( StorageHandler::BACKEND_TYPE_INSTANCE )
			);
			$this->assertNotInstanceOf(
				TempFSFileBackend::class, $handler->getBackend( StorageHandler::BACKEND_TYPE_INSTANCE )
			);

			$this->assertInstanceOf( InstanceTransaction::class, $handler->newInstanceTransaction() );
		}
	}

	/**
	 * @return array[]
	 */
	protected function provideHandlers() {
		$mainBackend = $this->createMock( FSFileBackend::class );
		$tempBackend = $this->createMock( TempFSFileBackend::class );
		$instanceBackend = $this->createMock( FileBackend::class );


		return [
			'no-instance' => [
				'handler' => new StorageHandler( $mainBackend, $tempBackend ),
				'hasInstance' => false,
			],
			'with-instance' => [
				'handler' => new StorageHandler( $mainBackend, $tempBackend, $instanceBackend ),
				'hasInstance' => true,
			]
		];
	}

	// Other public methods either invoke final methods of backend, or are tested in other tests.
}
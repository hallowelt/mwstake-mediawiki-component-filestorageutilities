<?php

namespace MWStake\MediaWiki\Component\FileStorageUtilities\Tests;

use MWStake\MediaWiki\Component\FileStorageUtilities\StorageTransaction;
use PHPUnit\Framework\TestCase;
use Wikimedia\FileBackend\FSFileBackend;

/**
 * @covers \MWStake\MediaWiki\Component\FileStorageUtilities\StorageTransaction
 */
class StorageTransactionTest extends TestCase {

	/**
	 * @covers \MWStake\MediaWiki\Component\FileStorageUtilities\StorageTransaction::create
	 * @covers \MWStake\MediaWiki\Component\FileStorageUtilities\StorageTransaction::store
	 * @covers \MWStake\MediaWiki\Component\FileStorageUtilities\StorageTransaction::move
	 * @covers \MWStake\MediaWiki\Component\FileStorageUtilities\StorageTransaction::copy
	 * @covers \MWStake\MediaWiki\Component\FileStorageUtilities\StorageTransaction::delete
	 *
	 * @return void
	 */
	public function testTransaction() {
		$backend = new FSFileBackend( [ 'name' => 'main-backend', 'domainId' => 'test' ] );
		$transaction = new StorageTransaction( $backend );

		$transaction
			->create( 'example.txt', 'This is a test file.', 'dummy/path' )
			->create( 'another.txt', 'Another test file.' )
			->store( '/local/path/file.txt', 'file.txt', 'store/path' )
			->move( 'oldname.txt', 'old/path', 'newname.txt', 'new/path' )
			->copy( 'copyme.txt', 'copy/from', 'copied.txt', 'copy/to' )
			->delete( 'tobedeleted.txt', 'delete/path' )
			->delete( 'anotherdelete.txt', '' );

		$this->assertSame( [
			// Creating - has path, so it needs to prepare
			[
				'op' => 'prepare',
				'dir' => 'mwstore://main-backend/bluespice/dummy/path',
			],
			[
				'op' => 'create',
				'dst' => 'mwstore://main-backend/bluespice/dummy/path/example.txt',
				'content' => 'This is a test file.',
			],
			// Creating - no path, so just create
			[
				'op' => 'create',
				'dst' => 'mwstore://main-backend/bluespice/another.txt',
				'content' => 'Another test file.',
			],
			// Storing
			[
				'op' => 'store',
				'src' => '/local/path/file.txt',
				'dst' => 'mwstore://main-backend/bluespice/store/path/file.txt',
			],
			// Moving
			[
				'op' => 'prepare',
				'dir' => 'mwstore://main-backend/bluespice/new/path',
			],
			[
				'op' => 'move',
				'src' => 'mwstore://main-backend/bluespice/old/path/oldname.txt',
				'dst' => 'mwstore://main-backend/bluespice/new/path/newname.txt',
			],
			// Copying
			[
				'op' => 'prepare',
				'dir' => 'mwstore://main-backend/bluespice/copy/to',
			],
			[
				'op' => 'copy',
				'src' => 'mwstore://main-backend/bluespice/copy/from/copyme.txt',
				'dst' => 'mwstore://main-backend/bluespice/copy/to/copied.txt',
			],
			// Deleting - with and without path
			[
				'op' => 'delete',
				'src' => 'mwstore://main-backend/bluespice/delete/path/tobedeleted.txt',
			],
			[
				'op' => 'delete',
				'src' => 'mwstore://main-backend/bluespice/anotherdelete.txt',
			]
		], $transaction->getOperations() );
	}
}

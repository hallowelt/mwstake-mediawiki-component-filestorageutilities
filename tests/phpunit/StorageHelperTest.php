<?php

namespace MWStake\MediaWiki\Component\FileStorageUtilities\Tests;

use MWStake\MediaWiki\Component\FileStorageUtilities\StorageHelper;
use PHPUnit\Framework\TestCase;
use Wikimedia\FileBackend\FSFileBackend;

/**
 * @covers \MWStake\MediaWiki\Component\FileStorageUtilities\StorageHelper
 */
class StorageHelperTest extends TestCase {

	/**
	 * @param string $filename
	 * @param string $path
	 * @param string $expected
	 * @covers \MWStake\MediaWiki\Component\FileStorageUtilities\StorageHelper::compileZonePath
	 * @return void
	 * @dataProvider provideZoneData
	 */
	public function testZonePaths( string $filename, string $path, string $expected ) {
		$backend = new FSFileBackend( [ 'name' => 'main-backend', 'domainId' => 'test' ] );
		$helper = new StorageHelper( $backend );

		$this->assertSame( $expected, $helper->compileZonePath( $path, $filename ) );
	}

	/**
	 * @return array[]
	 */
	protected function provideZoneData() {
		return [
			[ '', '', 'mwstore://main-backend/wiki_data' ],
			[ 'file.txt', '', 'mwstore://main-backend/wiki_data/file.txt' ],
			[ '', 'path/to/dir', 'mwstore://main-backend/wiki_data/path/to/dir' ],
			[ 'file.txt', 'path/to/dir', 'mwstore://main-backend/wiki_data/path/to/dir/file.txt' ],
			[ '/file.txt', '/path/to/dir/', 'mwstore://main-backend/wiki_data/path/to/dir/file.txt' ],
		];
	}
}

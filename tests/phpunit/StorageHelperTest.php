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
	 * @param string $instance
	 * @param string $file
	 * @param string $expected
	 * @return void
	 * @covers \MWStake\MediaWiki\Component\FileStorageUtilities\StorageHelper::makeInstancePath
	 * @dataProvider provideInstanceZoneData
	 */
	public function testInstancePaths( string $instance, string $file, string $expected ) {
		$backend = new FSFileBackend( [ 'name' => 'instance-backend', 'domainId' => 'test' ] );
		$helper = new StorageHelper( $backend );

		$this->assertSame( $expected, $helper->makeInstancePath( $instance, $file ) );
	}

	/**
	 * @param string $instance
	 * @param string $expected
	 * @return void
	 * @covers \MWStake\MediaWiki\Component\FileStorageUtilities\StorageHelper::makeArchiveInstancePath
	 * @dataProvider provideInstanceArchiveZoneData
	 */
	public function testInstanceArchivePaths( string $instance, string $expected ) {
		$backend = new FSFileBackend( [ 'name' => 'archive-backend', 'domainId' => 'test' ] );
		$helper = new StorageHelper( $backend );

		$this->assertSame( $expected, $helper->makeArchiveInstancePath( $instance ) );
	}

	/**
	 * @return array[]
	 */
	protected function provideZoneData() {
		return [
			[ '', '', 'mwstore://main-backend/bluespice' ],
			[ 'file.txt', '', 'mwstore://main-backend/bluespice/file.txt' ],
			[ '', 'path/to/dir', 'mwstore://main-backend/bluespice/path/to/dir' ],
			[ 'file.txt', 'path/to/dir', 'mwstore://main-backend/bluespice/path/to/dir/file.txt' ],
			[ '/file.txt', '/path/to/dir/', 'mwstore://main-backend/bluespice/path/to/dir/file.txt' ],
		];
	}

	/**
	 * @return array[]
	 */
	protected function provideInstanceZoneData() {
		return [
			[ 'instance1', '', 'mwstore://instance-backend/instances-public/instance1' ],
			[ 'instance1', 'file.txt', 'mwstore://instance-backend/instances-public/instance1/file.txt' ],
			[ '/instance1/', '/file.txt', 'mwstore://instance-backend/instances-public/instance1/file.txt' ],
		];
	}

	/**
	 * @return array[]
	 */
	protected function provideInstanceArchiveZoneData() {
		return [
			[ 'archive1', 'mwstore://archive-backend/archive-public/archive1' ],
			[ '/archive1/', 'mwstore://archive-backend/archive-public/archive1' ],
		];
	}
}

<?php

use MediaWiki\MainConfigNames;
use Wikimedia\FileBackend\FSFileBackend;

if ( defined( 'MWSTAKE_MEDIAWIKI_COMPONENT_FILESTORAGEUTILITIES_VERSION' ) ) {
	return;
}

define( 'MWSTAKE_MEDIAWIKI_COMPONENT_FILESTORAGEUTILITIES_VERSION', '1.0.1' );

MWStake\MediaWiki\ComponentLoader\Bootstrapper::getInstance()
->register( 'filestorageutilities', static function () {
	$GLOBALS['wgServiceWiringFiles'][] = __DIR__ . '/includes/ServiceWiring.php';

	$GLOBALS['wgFileBackends'] = $GLOBALS['wgFileBackends'] ?? [];
	if ( isset( $GLOBALS['wgWikiFarmConfigInternal' ] ) ) {
		// Set from wiki farm config if not set
		$GLOBALS['mwsgFileStorageInstancesDir'] =
			$GLOBALS['mwsgFileStorageInstancesDir'] ??
			$GLOBALS['wgWikiFarmConfigInternal' ]->get( 'instanceDirectory' );

		$GLOBALS['mwsgFileStorageArchiveDir'] =
			$GLOBALS['mwsgFileStorageArchiveDir'] ?? $GLOBALS['wgWikiFarmConfigInternal' ]->get( 'archiveDirectory' );
	}
	$GLOBALS['mwsgFileStorageBackend'] = null;

	$isS3 = $GLOBALS['mwsgFileStorageUseS3'] ?? false;
	$dirModeVariable = "wg" . MainConfigNames::DirectoryMode;

	if ( $isS3 ) {
		$GLOBALS['wgAWSRepoZones']['bluespice'] = [
			'container' => 'bluespice',
			'path' => '/bluespice',
			'isPublic' => false,
		];

		$GLOBALS['mwsgFileStorageBackend'] = $GLOBALS['mwsgFileStorageBackend'] ?? 'AmazonS3';

	} else {
		$GLOBALS['wgFileBackends']['bluespice'] = [
			'name' => 'bluespice-backend',
			'class' => FSFileBackend::class,
			'lockManager' => 'fsLockManager',
			'containerPaths' => [
				'bluespice' => defined( 'BS_DATA_DIR' ) ?
					BS_DATA_DIR :
					$GLOBALS['wgUploadDirectory'] . '/bluespice'
			],
			'fileMode' => $info['fileMode'] ?? 0644,
			'directoryMode' => $GLOBALS[$dirModeVariable],
		];
		$GLOBALS['mwsgFileStorageBackend'] = 'bluespice-backend';
	}

	// Local repo for temp files
	$GLOBALS['wgFileBackends']['bluespice-local'] = [
		'name' => 'bluespice-local-backend',
		'class' => \MWStake\MediaWiki\Component\FileStorageUtilities\TempFSFileBackend::class,
		'lockManager' => 'fsLockManager',
		'containerPaths' => [
			'bluespice' => $GLOBALS['mwsgFileStorageLocalTempDir'] ?? $GLOBALS['wgTmpDirectory'] . '/bluespice',
		],
		'fileMode' => $info['fileMode'] ?? 0644,
		'directoryMode' => $GLOBALS[$dirModeVariable],
	];
} );

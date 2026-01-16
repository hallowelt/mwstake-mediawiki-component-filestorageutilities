<?php

use MediaWiki\MainConfigNames;
use Wikimedia\FileBackend\FSFileBackend;

if ( defined( 'MWSTAKE_MEDIAWIKI_COMPONENT_FILESTORAGEUTILITIES_VERSION' ) ) {
	return;
}

define( 'MWSTAKE_MEDIAWIKI_COMPONENT_FILESTORAGEUTILITIES_VERSION', '1.0.3' );

MWStake\MediaWiki\ComponentLoader\Bootstrapper::getInstance()
->register( 'filestorageutilities', static function () {
	$GLOBALS['wgServiceWiringFiles'][] = __DIR__ . '/includes/ServiceWiring.php';

	$GLOBALS['wgFileBackends'] = $GLOBALS['wgFileBackends'] ?? [];
	$GLOBALS['mwsgFileStorageBackend'] = null;

	$isS3 = $GLOBALS['mwsgFileStorageUseS3'] ?? false;
	$dirModeVariable = "wg" . MainConfigNames::DirectoryMode;

	if ( $isS3 ) {
		$GLOBALS['wgAWSRepoZones']['wiki_data'] = [
			'container' => 'wiki_data',
			'path' => '/bluespice',
			'isPublic' => false,
		];

		$GLOBALS['mwsgFileStorageBackend'] = $GLOBALS['mwsgFileStorageBackend'] ?? 'AmazonS3';

	} else {
		$GLOBALS['wgFileBackends']['_data'] = [
			'name' => 'data-backend',
			'class' => FSFileBackend::class,
			'lockManager' => 'fsLockManager',
			'containerPaths' => [
				'wiki_data' => defined( 'BS_DATA_DIR' ) ?
					BS_DATA_DIR :
					$GLOBALS['wgUploadDirectory'] . '/bluespice'
			],
			'fileMode' => $info['fileMode'] ?? 0644,
			'directoryMode' => $GLOBALS[$dirModeVariable],
		];
		$GLOBALS['mwsgFileStorageBackend'] = 'data-backend';
	}

	// Local repo for temp files
	$GLOBALS['wgFileBackends']['data-local'] = [
		'name' => 'data-local-backend',
		'class' => \MWStake\MediaWiki\Component\FileStorageUtilities\TempFSFileBackend::class,
		'lockManager' => 'fsLockManager',
		'containerPaths' => [
			'wiki_data' => $GLOBALS['mwsgFileStorageLocalTempDir'] ?? $GLOBALS['wgTmpDirectory'] . '/wiki_temp_data',
		],
		'fileMode' => $info['fileMode'] ?? 0644,
		'directoryMode' => $GLOBALS[$dirModeVariable],
	];
} );

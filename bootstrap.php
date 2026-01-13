<?php

use MediaWiki\MainConfigNames;
use Wikimedia\FileBackend\FSFileBackend;

if ( defined( 'MWSTAKE_MEDIAWIKI_COMPONENT_FILESTORAGEUTILITIES_VERSION' ) ) {
	return;
}

define( 'MWSTAKE_MEDIAWIKI_COMPONENT_FILESTORAGEUTILITIES_VERSION', '1.0.0' );

MWStake\MediaWiki\ComponentLoader\Bootstrapper::getInstance()
->register( 'filestorageutilities', static function () {
	$GLOBALS['wgServiceWiringFiles'][] = __DIR__ . '/includes/ServiceWiring.php';

	$GLOBALS['wgFileBackends'] = $GLOBALS['wgFileBackends'] ?? [];
	$GLOBALS['mwsgFileStorageBackend'] = null;

	$isS3 = $GLOBALS['mwsgFileStorageUseS3'] ?? false;
	$dirModeVariable = "wg" . MainConfigNames::DirectoryMode;

	if ( $isS3 && !defined( 'MW_PHPUNIT_TEST' ) ) {
		$GLOBALS['wgAWSRepoZones']['bluespice'] = [
			'container' => 'bluespice',
			'path' => '/bluespice',
			'isPublic' => false,
		];

		$GLOBALS['mwsgFileStorageBackend'] = $GLOBALS['mwsgFileStorageBackend'] ?? 'AmazonS3';
		$GLOBALS['mwsgFileStorageGlobalBackend'] = $GLOBALS['mwsgFileStorageBackend'];
	} else {
		if ( !$GLOBALS['mwsgFileStorageBackend'] ) {
			$GLOBALS['wgFileBackends']['bluespice'] = [
				'name' => 'bluespice-backend',
				'class' => FSFileBackend::class,
				'lockManager' => 'fsLockManager',
				'containerPaths' => [
					'bluespice' => defined( 'BS_DATA_DIR' ) ? BS_DATA_DIR  : $GLOBALS['wgUploadDirectory'] . '/bluespice'
				],
				'fileMode' => $info['fileMode'] ?? 0644,
				'directoryMode' => $GLOBALS[$dirModeVariable],
			];
			$GLOBALS['mwsgFileStorageBackend'] = 'bluespice-backend';
		}

		if ( $GLOBALS['mwsgFileStorageGlobalRepoDir'] ?? false ) {
			$GLOBALS['wgFileBackends']['_global'] = [
				'name' => '_global',
				'class' => FSFileBackend::class,
				'lockManager' => 'fsLockManager',
				'containerPaths' => [
					'global-public' => $GLOBALS['mwsgFileStorageGlobalRepoDir'],
				],
				'fileMode' => $info['fileMode'] ?? 0644,
				'directoryMode' => $GLOBALS[$dirModeVariable],
			];
			$GLOBALS['mwsgFileStorageBackend'] = '_global';
		}
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

	$GLOBALS['wgHooks']['SetupAfterCache'][] = static function() use ( $isS3 ) {
		if ( $isS3 && !defined( 'MW_PHPUNIT_TEST' ) ) {
			// Setup "global" repo for farm. Actual bucket root
			$bucketName = $GLOBALS['wgAWSBucketName'];
			$wikiId = \MediaWiki\WikiMap\WikiMap::getCurrentWikiId();
			$GLOBALS['wgFileBackends']['s3']['containerPaths']["$wikiId-global-public"] = $bucketName;
		}

		/** @var \MWStake\MediaWiki\Component\FileStorageUtilities\StorageHandler $service */
		$service = \MediaWiki\MediaWikiServices::getInstance()
			->get( 'MWStake.StorageUtilities' );
	};
} );

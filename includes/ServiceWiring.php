<?php


return [
	'MWStake.StorageUtilities' => static function ( MediaWiki\MediaWikiServices $services ) {
		return new MWStake\MediaWiki\Component\FileStorageUtilities\StorageHandler(
			$services->getFileBackendGroup()->get( $GLOBALS['mwsgFileStorageBackend'] ),
			$services->getFileBackendGroup()->get( 'bluespice-local-backend' ),
			( $GLOBALS['mwsgFileStorageInstancesBackend'] ?? null ) ?
				$services->getFileBackendGroup()->get( $GLOBALS['mwsgFileStorageInstancesBackend'] ) : null
		);
	},
];

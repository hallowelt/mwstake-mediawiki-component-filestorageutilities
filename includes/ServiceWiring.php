<?php


return [
	'MWStake.StorageUtilities' => static function ( MediaWiki\MediaWikiServices $services ) {
		return new MWStake\MediaWiki\Component\FileStorageUtilities\StorageHandler(
			$services->getFileBackendGroup()->get( $GLOBALS['mwsgFileStorageBackend'] ),
			$services->getFileBackendGroup()->get( 'bluespice-local-backend' ),
			$GLOBALS['mwsgFileStorageBackend'] ?
				$services->getFileBackendGroup()->get( $GLOBALS['mwsgFileStorageBackend'] ) : null
		);
	},
];

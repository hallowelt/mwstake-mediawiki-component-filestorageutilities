<?php

return [
	'MWStake.StorageUtilities' => static function ( MediaWiki\MediaWikiServices $services ) {
		return new MWStake\MediaWiki\Component\FileStorageUtilities\StorageHandler(
			$services->getFileBackendGroup(),
			$GLOBALS['mwsgFileStorageBackend']
		);
	},
];

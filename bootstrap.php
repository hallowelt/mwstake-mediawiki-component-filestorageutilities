<?php

if ( defined( 'MWSTAKE_MEDIAWIKI_COMPONENT_FILESTORAGEUTILITIES_VERSION' ) ) {
	return;
}

define( 'MWSTAKE_MEDIAWIKI_COMPONENT_FILESTORAGEUTILITIES_VERSION', '1.0.0' );

MWStake\MediaWiki\ComponentLoader\Bootstrapper::getInstance()
->register( 'filestorageutilities', static function () {

} );

# MediaWiki Stakeholders Group - Components
# File Storage Utilities Component

## Usage

```php

/** @var \MWStake\MediaWiki\Component\FileStorageUtilities\StorageHandler $service */
$service = \MediaWiki\MediaWikiServices::getInstance()
    ->get( 'MWStake.StorageUtilities' );

$status = $service->newTransaction()
    ->create( 'testfile.txt', 'Content', 'SubZone/ABC', ['overwrite' => true ] )
    ->create( 'testfile2.txt', 'Content', 'SubZone/ABC', ['overwrite' => true ] )
    ->commit();

if ( !$status->isOK() ) {
    die( "FATAL: FileStorageUtilities could not create test file!" );
}

$file = $service->getFile( 'testfile.txt', 'SubZone/ABC' );
if ( !$file ) {
    die( "FATAL: FileStorageUtilities test file is missing or has wrong size!" );
}

$status = $service->newTransaction()
    ->delete( 'testfile.txt', 'SubZone/ABC' )
    ->commit();

if ( !$status->isOK() ) {
    die( "FATAL: FileStorageUtilities could not delete test file!" );
}

$service->newTransaction( useTempBackend: true )
    ->create( 'temp.txt', 'This is a temp file.', 'Test' )
    ->commit();
```

## Configuration

### AWS S3 backend ( AWS extension configured )
```php
// Before component is initialized (in pre-init)
$GLOBALS['mwsgFileStorageUseS3'] = true;
```

### FARM no-S3 backend
```php
// Configure this to the root directory holding instances
$GLOBALS['mwsgFileStorageInstancesDir'] = '/path/to/instances_root'
$GLOBALS['mwsgFileStorageArchiveDir'] = '/path/to/instances_root'
```

### Using custom backend (optional)
```php
$GLOBALS['mwsgFileStorageBackend'] = 'backend-name';
```

### Temp backend (optional)
In addition to main, persistent backend, a temporary backend can be used for transient files. This backend
saves files to a temp directory within container. Default: `wgTmpDirectory`

Note: Temp backend is shared between instances (in a farm setup), so no per-instance sub-dir.

```php
$GLOBALS['mwsgFileStorageLocalTempDir'] = '/path/to/temp/dir';
```

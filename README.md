# MediaWiki Stakeholders Group - Components
# File Storage Utilities Component

This component introduces different file backends to be used for persistent and temporary files:
- Main backend - persistent - could be S3 if system is so configured - shared between containers
- Temp backend - transient files - stored locally on disk (inside container in docker setup)

## Usage

This component relies on MediaWiki's FileBackend implementation and following methods are just convenience
methods wrapping around those.

### Shared - persistent files

```php
/** @var \MWStake\MediaWiki\Component\FileStorageUtilities\StorageHandler $service */
$service = \MediaWiki\MediaWikiServices::getInstance()
    ->get( 'MWStake.StorageUtilities' );    

// Create file
$status = $service->newTransaction()
    ->create( 'myfile.txt', 'File content', 'My/Sub/Directory', [ 'overwrite' => true ] )
    ->commit();

// Retrieve file
$file = $service->getFile( 'myfile.txt', 'My/Sub/Directory' );
$file->getFilename(); // 'myfile.txt'
$file->getDirectory(); // 'My/Sub/Directory'
$file->getPath(); // Local copy => '/tmp/{hash}.txt'
$content = file_get_contents( $file->getPath() ); // 'File content'

// Delete file
$status = $service->newTransaction()
    ->delete( 'myfile.txt', 'My/Sub/Directory' )
    ->deleteDirectory( 'My/Sub/Directory' )
    ->commit();
```

### Temporary files

```php
/** @var \MWStake\MediaWiki\Component\FileStorageUtilities\StorageHandler $service */
$service = \MediaWiki\MediaWikiServices::getInstance()
    ->get( 'MWStake.StorageUtilities' );

// Create temp file
$status = $service->newTempTransaction()
    ->create( 'mytempfile.txt', 'Temp file content', 'Temp/Dir' )
    ->commit();
       
// Get path of the temp file without creating it - useful for passing to objects that will write to that path
$prepare = true; // default: true to create any missing directories in path, false to just return the path
$path = $service->getTempFilePath( 'mytempfile2.txt', 'Temp/Dir', $prepare ); // configured/temp/dir/Temp/Dir/mytempfile2.txt    

// Retrieving/deleting files is same as for persistent files
```

### Direct FileBackend usage
In edge-cases where wrapper methods are not enough, you can use FileBackends directly

```php
/** @var \MWStake\MediaWiki\Component\FileStorageUtilities\StorageHandler $service */
$service = \MediaWiki\MediaWikiServices::getInstance()
    ->get( 'MWStake.StorageUtilities' );

$type = 'main' // Shared
$type = 'temp' // Temp
$type = 'instance' // Instance

$backend = $service->getBackend( $type );
````

## Configuration
Settings are to be configured in pre-init!

### AWS S3 backend ( only set to true if AWS extension is configured )
```php
$GLOBALS['mwsgFileStorageUseS3'] = true;
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

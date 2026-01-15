<?php

namespace MWStake\MediaWiki\Component\FileStorageUtilities;

use FSFile;

class StoredFile {

	/**
	 * We are storing original FSFile, as we need to keep reference to is, otherwise it will be removed
	 *
	 * @param FSFile $file
	 * @param string $fileBackend
	 * @param string $name
	 * @param string $virtualPath
	 */
	public function __construct(
		private readonly FSFile $file,
		private readonly string $fileBackend,
		private readonly string $name,
		private readonly string $virtualPath
	) {
	}

	/**
	 * @return string|null
	 */
	public function getContent(): ?string {
		$content = file_get_contents( $this->getPath() );
		if ( $content === false ) {
			return null;
		}

		return $content;
	}

	/**
	 * @return string
	 */
	public function getFilename(): string {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getPath(): string {
		return $this->file->getPath();
	}

	/**
	 * Directory of the file, relative to the backend
	 * @return string
	 */
	public function getDirectory(): string {
		return $this->virtualPath;
	}

	/**
	 * @return string
	 */
	public function getExtension(): string {
		return pathinfo( $this->getPath(), PATHINFO_EXTENSION );
	}

	public function getOriginBackend(): string {
		return $this->fileBackend;
	}

}

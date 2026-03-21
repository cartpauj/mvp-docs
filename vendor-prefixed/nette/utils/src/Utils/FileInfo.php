<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 *
 * @license BSD-3-Clause,GPL-2.0-only,GPL-3.0-only
 * Modified by __root__ on 21-March-2026 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace MvpDocs\Vendor\Nette\Utils;

use MvpDocs\Vendor\Nette;
use const DIRECTORY_SEPARATOR;


/**
 * Represents the file or directory returned by the Finder.
 * @internal do not create instances directly
 */
final class FileInfo extends \SplFileInfo
{
	public function __construct(
		string $file,
		private readonly string $relativePath = '',
	) {
		parent::__construct($file);
		$this->setInfoClass(self::class);
	}


	/**
	 * Returns the relative directory path.
	 */
	public function getRelativePath(): string
	{
		return $this->relativePath;
	}


	/**
	 * Returns the relative path including file name.
	 */
	public function getRelativePathname(): string
	{
		return ($this->relativePath === '' ? '' : $this->relativePath . DIRECTORY_SEPARATOR)
			. $this->getBasename();
	}


	/**
	 * Returns the contents of the file.
	 * @throws MvpDocs\Vendor\Nette\IOException
	 */
	public function read(): string
	{
		return FileSystem::read($this->getPathname());
	}


	/**
	 * Writes the contents to the file.
	 * @throws MvpDocs\Vendor\Nette\IOException
	 */
	public function write(string $content): void
	{
		FileSystem::write($this->getPathname(), $content);
	}
}

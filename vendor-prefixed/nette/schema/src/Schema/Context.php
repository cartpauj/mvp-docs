<?php
/**
 * @license BSD-3-Clause,GPL-2.0-only,GPL-3.0-only
 *
 * Modified by __root__ on 21-March-2026 using {@see https://github.com/BrianHenryIE/strauss}.
 */ declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace MvpDocs\Vendor\Nette\Schema;

use function count;


final class Context
{
	public bool $skipDefaults = false;

	/** @var list<int|string> */
	public array $path = [];

	public bool $isKey = false;

	/** @var list<Message> */
	public array $errors = [];

	/** @var list<Message> */
	public array $warnings = [];

	/** @var list<array{DynamicParameter, string, list<int|string>}> */
	public array $dynamics = [];


	/** @param  array<string, mixed>  $variables */
	public function addError(string $message, string $code, array $variables = []): Message
	{
		$variables['isKey'] = $this->isKey;
		return $this->errors[] = new Message($message, $code, $this->path, $variables);
	}


	/** @param  array<string, mixed>  $variables */
	public function addWarning(string $message, string $code, array $variables = []): Message
	{
		return $this->warnings[] = new Message($message, $code, $this->path, $variables);
	}


	/** @return \Closure(): bool */
	public function createChecker(): \Closure
	{
		$count = count($this->errors);
		return fn(): bool => $count === count($this->errors);
	}
}

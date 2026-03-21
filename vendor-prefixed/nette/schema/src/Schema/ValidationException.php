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

use MvpDocs\Vendor\Nette;


/**
 * Validation error.
 */
class ValidationException extends MvpDocs\Vendor\Nette\InvalidStateException
{
	public function __construct(
		?string $message,
		/** @var list<Message> */
		private array $messages = [],
	) {
		parent::__construct($message ?? $messages[0]->toString());
	}


	/** @return list<string> */
	public function getMessages(): array
	{
		$res = [];
		foreach ($this->messages as $message) {
			$res[] = $message->toString();
		}

		return $res;
	}


	/** @return list<Message> */
	public function getMessageObjects(): array
	{
		return $this->messages;
	}
}

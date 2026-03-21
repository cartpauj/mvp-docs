<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 *
 * @license BSD-3-Clause,GPL-2.0-only,GPL-3.0-only
 * Modified by __root__ on 21-March-2026 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace MvpDocs\Vendor\Nette\Iterators;


/**
 * @deprecated use MvpDocs\Vendor\Nette\Utils\Iterables::map()
 */
class Mapper extends \IteratorIterator
{
	private \Closure $callback;


	public function __construct(\Traversable $iterator, callable $callback)
	{
		parent::__construct($iterator);
		$this->callback = $callback(...);
	}


	public function current(): mixed
	{
		return ($this->callback)(parent::current(), parent::key());
	}
}

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


interface Schema
{
	/**
	 * Normalization.
	 * @return mixed
	 */
	function normalize(mixed $value, Context $context);

	/**
	 * Merging.
	 * @return mixed
	 */
	function merge(mixed $value, mixed $base);

	/**
	 * Validation and finalization.
	 * @return mixed
	 */
	function complete(mixed $value, Context $context);

	/**
	 * @return mixed
	 */
	function completeDefault(Context $context);
}

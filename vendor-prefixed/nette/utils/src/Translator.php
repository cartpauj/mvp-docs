<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 *
 * @license BSD-3-Clause,GPL-2.0-only,GPL-3.0-only
 * Modified by __root__ on 21-March-2026 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace MvpDocs\Vendor\Nette\Localization;


/**
 * Translation provider.
 */
interface Translator
{
	/**
	 * Translates the given string.
	 */
	function translate(string|\Stringable $message, mixed ...$parameters): string|\Stringable;
}


interface_exists(ITranslator::class);

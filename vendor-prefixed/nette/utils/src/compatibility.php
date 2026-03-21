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

if (false) {
	/** @deprecated use MvpDocs\Vendor\Nette\HtmlStringable */
	interface IHtmlString extends MvpDocs\Vendor\Nette\HtmlStringable
	{
	}
} elseif (!interface_exists(IHtmlString::class)) {
	class_alias(MvpDocs\Vendor\Nette\HtmlStringable::class, IHtmlString::class);
}

namespace MvpDocs\Vendor\Nette\Localization;

if (false) {
	/** @deprecated use MvpDocs\Vendor\Nette\Localization\Translator */
	interface ITranslator extends Translator
	{
	}
} elseif (!interface_exists(ITranslator::class)) {
	class_alias(Translator::class, ITranslator::class);
}

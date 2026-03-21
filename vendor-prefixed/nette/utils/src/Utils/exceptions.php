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


/**
 * An error occurred while working with the image.
 */
class ImageException extends \Exception
{
}


/**
 * The image file is invalid or in an unsupported format.
 */
class UnknownImageFileException extends ImageException
{
}


/**
 * JSON encoding or decoding failed.
 */
class JsonException extends \JsonException
{
}


/**
 * Regular expression pattern or execution failed.
 */
class RegexpException extends \Exception
{
}


/**
 * Type validation failed. The value doesn't match the expected type constraints.
 */
class AssertionException extends \Exception
{
}

<?php

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Modified by __root__ on 21-March-2026 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace MvpDocs\Vendor\League\CommonMark\Extension\FrontMatter\Exception;

use MvpDocs\Vendor\League\CommonMark\Exception\CommonMarkException;

class InvalidFrontMatterException extends \RuntimeException implements CommonMarkException
{
    public static function wrap(\Throwable $t): self
    {
        return new InvalidFrontMatterException('Failed to parse front matter: ' . $t->getMessage(), 0, $t);
    }
}

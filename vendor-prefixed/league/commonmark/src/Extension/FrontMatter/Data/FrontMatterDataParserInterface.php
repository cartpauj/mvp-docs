<?php
/**
 * @license BSD-3-Clause
 *
 * Modified by __root__ on 21-March-2026 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MvpDocs\Vendor\League\CommonMark\Extension\FrontMatter\Data;

use MvpDocs\Vendor\League\CommonMark\Extension\FrontMatter\Exception\InvalidFrontMatterException;

interface FrontMatterDataParserInterface
{
    /**
     * @return mixed|null The parsed data (which may be null, if the input represents a null value)
     *
     * @throws InvalidFrontMatterException if parsing fails
     */
    public function parse(string $frontMatter);
}

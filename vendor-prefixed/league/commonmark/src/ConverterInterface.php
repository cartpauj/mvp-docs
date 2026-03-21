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

namespace MvpDocs\Vendor\League\CommonMark;

use MvpDocs\Vendor\League\CommonMark\Exception\CommonMarkException;
use MvpDocs\Vendor\League\CommonMark\Output\RenderedContentInterface;
use MvpDocs\Vendor\League\Config\Exception\ConfigurationExceptionInterface;

/**
 * Interface for a service which converts content from one format (like Markdown) to another (like HTML).
 */
interface ConverterInterface
{
    /**
     * @throws CommonMarkException
     * @throws ConfigurationExceptionInterface
     */
    public function convert(string $input): RenderedContentInterface;
}

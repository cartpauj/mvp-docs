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

namespace MvpDocs\Vendor\League\CommonMark\Output;

use MvpDocs\Vendor\League\CommonMark\Node\Block\Document;

interface RenderedContentInterface extends \Stringable
{
    /**
     * @psalm-mutation-free
     */
    public function getDocument(): Document;

    /**
     * @psalm-mutation-free
     */
    public function getContent(): string;
}

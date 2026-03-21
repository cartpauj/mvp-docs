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

namespace MvpDocs\Vendor\League\CommonMark\Extension\CommonMark\Parser\Block;

use MvpDocs\Vendor\League\CommonMark\Extension\CommonMark\Node\Block\ThematicBreak;
use MvpDocs\Vendor\League\CommonMark\Parser\Block\AbstractBlockContinueParser;
use MvpDocs\Vendor\League\CommonMark\Parser\Block\BlockContinue;
use MvpDocs\Vendor\League\CommonMark\Parser\Block\BlockContinueParserInterface;
use MvpDocs\Vendor\League\CommonMark\Parser\Cursor;

final class ThematicBreakParser extends AbstractBlockContinueParser
{
    /** @psalm-readonly */
    private ThematicBreak $block;

    public function __construct()
    {
        $this->block = new ThematicBreak();
    }

    public function getBlock(): ThematicBreak
    {
        return $this->block;
    }

    public function tryContinue(Cursor $cursor, BlockContinueParserInterface $activeBlockParser): ?BlockContinue
    {
        // a horizontal rule can never container > 1 line, so fail to match
        return BlockContinue::none();
    }
}

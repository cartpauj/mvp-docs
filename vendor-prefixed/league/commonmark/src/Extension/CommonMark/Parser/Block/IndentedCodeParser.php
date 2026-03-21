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

use MvpDocs\Vendor\League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use MvpDocs\Vendor\League\CommonMark\Parser\Block\AbstractBlockContinueParser;
use MvpDocs\Vendor\League\CommonMark\Parser\Block\BlockContinue;
use MvpDocs\Vendor\League\CommonMark\Parser\Block\BlockContinueParserInterface;
use MvpDocs\Vendor\League\CommonMark\Parser\Cursor;
use MvpDocs\Vendor\League\CommonMark\Util\ArrayCollection;

final class IndentedCodeParser extends AbstractBlockContinueParser
{
    /** @psalm-readonly */
    private IndentedCode $block;

    /** @var ArrayCollection<string> */
    private ArrayCollection $strings;

    public function __construct()
    {
        $this->block   = new IndentedCode();
        $this->strings = new ArrayCollection();
    }

    public function getBlock(): IndentedCode
    {
        return $this->block;
    }

    public function tryContinue(Cursor $cursor, BlockContinueParserInterface $activeBlockParser): ?BlockContinue
    {
        if ($cursor->isIndented()) {
            $cursor->advanceBy(Cursor::INDENT_LEVEL, true);

            return BlockContinue::at($cursor);
        }

        if ($cursor->isBlank()) {
            $cursor->advanceToNextNonSpaceOrTab();

            return BlockContinue::at($cursor);
        }

        return BlockContinue::none();
    }

    public function addLine(string $line): void
    {
        $this->strings[] = $line;
    }

    public function closeBlock(): void
    {
        $lines = $this->strings->toArray();

        // Note that indented code block cannot be empty, so $lines will always have at least one non-empty element
        while (\preg_match('/^[ \t]*$/', \end($lines))) { // @phpstan-ignore-line
            \array_pop($lines);
        }

        $this->block->setLiteral(\implode("\n", $lines) . "\n");
        $this->block->setEndLine($this->block->getStartLine() + \count($lines) - 1);
    }
}

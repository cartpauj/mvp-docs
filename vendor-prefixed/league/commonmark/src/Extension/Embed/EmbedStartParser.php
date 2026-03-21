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

namespace MvpDocs\Vendor\League\CommonMark\Extension\Embed;

use MvpDocs\Vendor\League\CommonMark\Parser\Block\BlockStart;
use MvpDocs\Vendor\League\CommonMark\Parser\Block\BlockStartParserInterface;
use MvpDocs\Vendor\League\CommonMark\Parser\Cursor;
use MvpDocs\Vendor\League\CommonMark\Parser\MarkdownParserStateInterface;
use MvpDocs\Vendor\League\CommonMark\Util\LinkParserHelper;

class EmbedStartParser implements BlockStartParserInterface
{
    public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart
    {
        if ($cursor->isIndented() || $parserState->getParagraphContent() !== null || ! ($parserState->getActiveBlockParser()->isContainer())) {
            return BlockStart::none();
        }

        // 0-3 leading spaces are okay
        $cursor->advanceToNextNonSpaceOrTab();

        // The line must begin with "https://"
        if (! str_starts_with($cursor->getRemainder(), 'https://')) {
            return BlockStart::none();
        }

        // A valid link must be found next
        if (($dest = LinkParserHelper::parseLinkDestination($cursor)) === null) {
            return BlockStart::none();
        }

        // Skip any trailing whitespace
        $cursor->advanceToNextNonSpaceOrTab();

        // We must be at the end of the line; otherwise, this link was not by itself
        if (! $cursor->isAtEnd()) {
            return BlockStart::none();
        }

        return BlockStart::of(new EmbedParser($dest))->at($cursor);
    }
}

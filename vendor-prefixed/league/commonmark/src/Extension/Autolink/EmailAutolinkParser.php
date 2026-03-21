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

namespace MvpDocs\Vendor\League\CommonMark\Extension\Autolink;

use MvpDocs\Vendor\League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use MvpDocs\Vendor\League\CommonMark\Parser\Inline\InlineParserInterface;
use MvpDocs\Vendor\League\CommonMark\Parser\Inline\InlineParserMatch;
use MvpDocs\Vendor\League\CommonMark\Parser\InlineParserContext;

final class EmailAutolinkParser implements InlineParserInterface
{
    private const REGEX = '[A-Za-z0-9.\-_+]+@[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_.]+';

    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::regex(self::REGEX);
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $email = $inlineContext->getFullMatch();
        // The last character cannot be - or _
        if (\in_array(\substr($email, -1), ['-', '_'], true)) {
            return false;
        }

        // Does the URL end with punctuation that should be stripped?
        if (\substr($email, -1) === '.') {
            $email = \substr($email, 0, -1);
        }

        $inlineContext->getCursor()->advanceBy(\strlen($email));
        $inlineContext->getContainer()->appendChild(new Link('mailto:' . $email, $email));

        return true;
    }
}

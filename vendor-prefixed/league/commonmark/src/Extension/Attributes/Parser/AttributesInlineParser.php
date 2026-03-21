<?php

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 * (c) 2015 Martin Hasoň <martin.hason@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Modified by __root__ on 21-March-2026 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace MvpDocs\Vendor\League\CommonMark\Extension\Attributes\Parser;

use MvpDocs\Vendor\League\CommonMark\Extension\Attributes\Node\AttributesInline;
use MvpDocs\Vendor\League\CommonMark\Extension\Attributes\Util\AttributesHelper;
use MvpDocs\Vendor\League\CommonMark\Node\StringContainerInterface;
use MvpDocs\Vendor\League\CommonMark\Parser\Inline\InlineParserInterface;
use MvpDocs\Vendor\League\CommonMark\Parser\Inline\InlineParserMatch;
use MvpDocs\Vendor\League\CommonMark\Parser\InlineParserContext;

final class AttributesInlineParser implements InlineParserInterface
{
    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::string('{');
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $cursor = $inlineContext->getCursor();
        $char   = (string) $cursor->peek(-1);

        $attributes = AttributesHelper::parseAttributes($cursor);
        if ($attributes === []) {
            return false;
        }

        if ($char === ' ' && ($prev = $inlineContext->getContainer()->lastChild()) instanceof StringContainerInterface) {
            $prev->setLiteral(\rtrim($prev->getLiteral(), ' '));
        }

        if ($char === '') {
            $cursor->advanceToNextNonSpaceOrNewline();
        }

        $node = new AttributesInline($attributes, $char === ' ' || $char === '');
        $inlineContext->getContainer()->appendChild($node);

        return true;
    }
}

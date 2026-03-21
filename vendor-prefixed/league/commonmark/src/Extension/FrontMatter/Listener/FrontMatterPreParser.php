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

namespace MvpDocs\Vendor\League\CommonMark\Extension\FrontMatter\Listener;

use MvpDocs\Vendor\League\CommonMark\Event\DocumentPreParsedEvent;
use MvpDocs\Vendor\League\CommonMark\Extension\FrontMatter\FrontMatterParserInterface;

final class FrontMatterPreParser
{
    private FrontMatterParserInterface $parser;

    public function __construct(FrontMatterParserInterface $parser)
    {
        $this->parser = $parser;
    }

    public function __invoke(DocumentPreParsedEvent $event): void
    {
        $content = $event->getMarkdown()->getContent();

        $parsed = $this->parser->parse($content);

        $event->getDocument()->data->set('front_matter', $parsed->getFrontMatter());
        $event->replaceMarkdown($parsed);
    }
}

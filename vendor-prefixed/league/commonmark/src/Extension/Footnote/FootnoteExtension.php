<?php

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 * (c) Rezo Zero / Ambroise Maupate
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Modified by __root__ on 21-March-2026 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace MvpDocs\Vendor\League\CommonMark\Extension\Footnote;

use MvpDocs\Vendor\League\CommonMark\Environment\EnvironmentBuilderInterface;
use MvpDocs\Vendor\League\CommonMark\Event\DocumentParsedEvent;
use MvpDocs\Vendor\League\CommonMark\Extension\ConfigurableExtensionInterface;
use MvpDocs\Vendor\League\CommonMark\Extension\Footnote\Event\AnonymousFootnotesListener;
use MvpDocs\Vendor\League\CommonMark\Extension\Footnote\Event\FixOrphanedFootnotesAndRefsListener;
use MvpDocs\Vendor\League\CommonMark\Extension\Footnote\Event\GatherFootnotesListener;
use MvpDocs\Vendor\League\CommonMark\Extension\Footnote\Event\NumberFootnotesListener;
use MvpDocs\Vendor\League\CommonMark\Extension\Footnote\Node\Footnote;
use MvpDocs\Vendor\League\CommonMark\Extension\Footnote\Node\FootnoteBackref;
use MvpDocs\Vendor\League\CommonMark\Extension\Footnote\Node\FootnoteContainer;
use MvpDocs\Vendor\League\CommonMark\Extension\Footnote\Node\FootnoteRef;
use MvpDocs\Vendor\League\CommonMark\Extension\Footnote\Parser\AnonymousFootnoteRefParser;
use MvpDocs\Vendor\League\CommonMark\Extension\Footnote\Parser\FootnoteRefParser;
use MvpDocs\Vendor\League\CommonMark\Extension\Footnote\Parser\FootnoteStartParser;
use MvpDocs\Vendor\League\CommonMark\Extension\Footnote\Renderer\FootnoteBackrefRenderer;
use MvpDocs\Vendor\League\CommonMark\Extension\Footnote\Renderer\FootnoteContainerRenderer;
use MvpDocs\Vendor\League\CommonMark\Extension\Footnote\Renderer\FootnoteRefRenderer;
use MvpDocs\Vendor\League\CommonMark\Extension\Footnote\Renderer\FootnoteRenderer;
use MvpDocs\Vendor\League\Config\ConfigurationBuilderInterface;
use MvpDocs\Vendor\Nette\Schema\Expect;

final class FootnoteExtension implements ConfigurableExtensionInterface
{
    public function configureSchema(ConfigurationBuilderInterface $builder): void
    {
        $builder->addSchema('footnote', Expect::structure([
            'backref_class' => Expect::string('footnote-backref'),
            'backref_symbol' => Expect::string('↩'),
            'container_add_hr' => Expect::bool(true),
            'container_class' => Expect::string('footnotes'),
            'ref_class' => Expect::string('footnote-ref'),
            'ref_id_prefix' => Expect::string('fnref:'),
            'footnote_class' => Expect::string('footnote'),
            'footnote_id_prefix' => Expect::string('fn:'),
        ]));
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addBlockStartParser(new FootnoteStartParser(), 51);
        $environment->addInlineParser(new AnonymousFootnoteRefParser(), 35);
        $environment->addInlineParser(new FootnoteRefParser(), 51);

        $environment->addRenderer(FootnoteContainer::class, new FootnoteContainerRenderer());
        $environment->addRenderer(Footnote::class, new FootnoteRenderer());
        $environment->addRenderer(FootnoteRef::class, new FootnoteRefRenderer());
        $environment->addRenderer(FootnoteBackref::class, new FootnoteBackrefRenderer());

        $environment->addEventListener(DocumentParsedEvent::class, [new AnonymousFootnotesListener(), 'onDocumentParsed'], 40);
        $environment->addEventListener(DocumentParsedEvent::class, [new FixOrphanedFootnotesAndRefsListener(), 'onDocumentParsed'], 30);
        $environment->addEventListener(DocumentParsedEvent::class, [new NumberFootnotesListener(), 'onDocumentParsed'], 20);
        $environment->addEventListener(DocumentParsedEvent::class, [new GatherFootnotesListener(), 'onDocumentParsed'], 10);
    }
}

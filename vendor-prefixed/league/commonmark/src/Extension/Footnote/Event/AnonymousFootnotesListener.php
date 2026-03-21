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

namespace MvpDocs\Vendor\League\CommonMark\Extension\Footnote\Event;

use MvpDocs\Vendor\League\CommonMark\Event\DocumentParsedEvent;
use MvpDocs\Vendor\League\CommonMark\Extension\Footnote\Node\Footnote;
use MvpDocs\Vendor\League\CommonMark\Extension\Footnote\Node\FootnoteBackref;
use MvpDocs\Vendor\League\CommonMark\Extension\Footnote\Node\FootnoteRef;
use MvpDocs\Vendor\League\CommonMark\Node\Block\Paragraph;
use MvpDocs\Vendor\League\CommonMark\Node\Inline\Text;
use MvpDocs\Vendor\League\CommonMark\Reference\Reference;
use MvpDocs\Vendor\League\Config\ConfigurationAwareInterface;
use MvpDocs\Vendor\League\Config\ConfigurationInterface;

final class AnonymousFootnotesListener implements ConfigurationAwareInterface
{
    private ConfigurationInterface $config;

    public function onDocumentParsed(DocumentParsedEvent $event): void
    {
        $document = $event->getDocument();
        foreach ($document->iterator() as $node) {
            if (! $node instanceof FootnoteRef || ($text = $node->getContent()) === null) {
                continue;
            }

            // Anonymous footnote needs to create a footnote from its content
            $existingReference = $node->getReference();
            $newReference      = new Reference(
                $existingReference->getLabel(),
                '#' . $this->config->get('footnote/ref_id_prefix') . $existingReference->getLabel(),
                $existingReference->getTitle()
            );

            $paragraph = new Paragraph();
            $paragraph->appendChild(new Text($text));
            $paragraph->appendChild(new FootnoteBackref($newReference));

            $footnote = new Footnote($newReference);
            $footnote->appendChild($paragraph);

            $document->appendChild($footnote);
        }
    }

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->config = $configuration;
    }
}

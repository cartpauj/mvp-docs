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

namespace MvpDocs\Vendor\League\CommonMark\Extension\Footnote\Renderer;

use MvpDocs\Vendor\League\CommonMark\Extension\Footnote\Node\FootnoteContainer;
use MvpDocs\Vendor\League\CommonMark\Node\Node;
use MvpDocs\Vendor\League\CommonMark\Renderer\ChildNodeRendererInterface;
use MvpDocs\Vendor\League\CommonMark\Renderer\NodeRendererInterface;
use MvpDocs\Vendor\League\CommonMark\Util\HtmlElement;
use MvpDocs\Vendor\League\CommonMark\Xml\XmlNodeRendererInterface;
use MvpDocs\Vendor\League\Config\ConfigurationAwareInterface;
use MvpDocs\Vendor\League\Config\ConfigurationInterface;

final class FootnoteContainerRenderer implements NodeRendererInterface, XmlNodeRendererInterface, ConfigurationAwareInterface
{
    private ConfigurationInterface $config;

    /**
     * @param FootnoteContainer $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable
    {
        FootnoteContainer::assertInstanceOf($node);

        $attrs = $node->data->getData('attributes');

        $attrs->append('class', $this->config->get('footnote/container_class'));
        $attrs->set('role', 'doc-endnotes');

        $contents = new HtmlElement('ol', [], $childRenderer->renderNodes($node->children()));
        if ($this->config->get('footnote/container_add_hr')) {
            $contents = [new HtmlElement('hr', [], null, true), $contents];
        }

        return new HtmlElement('div', $attrs->export(), $contents);
    }

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->config = $configuration;
    }

    public function getXmlTagName(Node $node): string
    {
        return 'footnote_container';
    }

    /**
     * @return array<string, scalar>
     */
    public function getXmlAttributes(Node $node): array
    {
        return [];
    }
}

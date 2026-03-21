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
 * (c) Colin O'Dell <colinodell@gmail.com> and uAfrica.com (http://uafrica.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MvpDocs\Vendor\League\CommonMark\Extension\Strikethrough;

use MvpDocs\Vendor\League\CommonMark\Node\Node;
use MvpDocs\Vendor\League\CommonMark\Renderer\ChildNodeRendererInterface;
use MvpDocs\Vendor\League\CommonMark\Renderer\NodeRendererInterface;
use MvpDocs\Vendor\League\CommonMark\Util\HtmlElement;
use MvpDocs\Vendor\League\CommonMark\Xml\XmlNodeRendererInterface;

final class StrikethroughRenderer implements NodeRendererInterface, XmlNodeRendererInterface
{
    /**
     * @param Strikethrough $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable
    {
        Strikethrough::assertInstanceOf($node);

        return new HtmlElement('del', $node->data->get('attributes'), $childRenderer->renderNodes($node->children()));
    }

    public function getXmlTagName(Node $node): string
    {
        return 'strikethrough';
    }

    /**
     * {@inheritDoc}
     */
    public function getXmlAttributes(Node $node): array
    {
        return [];
    }
}

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
 * Original code based on the CommonMark JS reference parser (https://bitly.com/commonmark-js)
 *  - (c) John MacFarlane
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MvpDocs\Vendor\League\CommonMark\Renderer\Inline;

use MvpDocs\Vendor\League\CommonMark\Node\Inline\Text;
use MvpDocs\Vendor\League\CommonMark\Node\Node;
use MvpDocs\Vendor\League\CommonMark\Renderer\ChildNodeRendererInterface;
use MvpDocs\Vendor\League\CommonMark\Renderer\NodeRendererInterface;
use MvpDocs\Vendor\League\CommonMark\Util\Xml;
use MvpDocs\Vendor\League\CommonMark\Xml\XmlNodeRendererInterface;

final class TextRenderer implements NodeRendererInterface, XmlNodeRendererInterface
{
    /**
     * @param Text $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        Text::assertInstanceOf($node);

        return Xml::escape($node->getLiteral());
    }

    public function getXmlTagName(Node $node): string
    {
        return 'text';
    }

    /**
     * {@inheritDoc}
     */
    public function getXmlAttributes(Node $node): array
    {
        return [];
    }
}

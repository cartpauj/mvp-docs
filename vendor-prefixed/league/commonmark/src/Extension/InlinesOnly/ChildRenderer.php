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

namespace MvpDocs\Vendor\League\CommonMark\Extension\InlinesOnly;

use MvpDocs\Vendor\League\CommonMark\Node\Block\Document;
use MvpDocs\Vendor\League\CommonMark\Node\Node;
use MvpDocs\Vendor\League\CommonMark\Renderer\ChildNodeRendererInterface;
use MvpDocs\Vendor\League\CommonMark\Renderer\NodeRendererInterface;

/**
 * Simply renders child elements as-is, adding newlines as needed.
 */
final class ChildRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        $out = $childRenderer->renderNodes($node->children());
        if (! $node instanceof Document) {
            $out .= $childRenderer->getBlockSeparator();
        }

        return $out;
    }
}

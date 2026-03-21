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

namespace MvpDocs\Vendor\League\CommonMark\Renderer;

use MvpDocs\Vendor\League\CommonMark\Exception\InvalidArgumentException;
use MvpDocs\Vendor\League\CommonMark\Node\Node;

interface NodeRendererInterface
{
    /**
     * @return \Stringable|string|null
     *
     * @throws InvalidArgumentException if the wrong type of Node is provided
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer);
}

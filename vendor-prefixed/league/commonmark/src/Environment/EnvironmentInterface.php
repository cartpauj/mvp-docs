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

namespace MvpDocs\Vendor\League\CommonMark\Environment;

use MvpDocs\Vendor\League\CommonMark\Delimiter\Processor\DelimiterProcessorCollection;
use MvpDocs\Vendor\League\CommonMark\Extension\ExtensionInterface;
use MvpDocs\Vendor\League\CommonMark\Node\Node;
use MvpDocs\Vendor\League\CommonMark\Normalizer\TextNormalizerInterface;
use MvpDocs\Vendor\League\CommonMark\Parser\Block\BlockStartParserInterface;
use MvpDocs\Vendor\League\CommonMark\Parser\Inline\InlineParserInterface;
use MvpDocs\Vendor\League\CommonMark\Renderer\NodeRendererInterface;
use MvpDocs\Vendor\League\Config\ConfigurationProviderInterface;
use MvpDocs\Vendor\Psr\EventDispatcher\EventDispatcherInterface;

interface EnvironmentInterface extends ConfigurationProviderInterface, EventDispatcherInterface
{
    /**
     * Get all registered extensions
     *
     * @return ExtensionInterface[]
     */
    public function getExtensions(): iterable;

    /**
     * @return iterable<BlockStartParserInterface>
     */
    public function getBlockStartParsers(): iterable;

    /**
     * @return iterable<InlineParserInterface>
     */
    public function getInlineParsers(): iterable;

    public function getDelimiterProcessors(): DelimiterProcessorCollection;

    /**
     * @psalm-param class-string<Node> $nodeClass
     *
     * @return iterable<NodeRendererInterface>
     */
    public function getRenderersForClass(string $nodeClass): iterable;

    public function getSlugNormalizer(): TextNormalizerInterface;
}

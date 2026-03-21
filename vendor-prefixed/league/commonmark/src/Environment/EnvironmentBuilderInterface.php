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

use MvpDocs\Vendor\League\CommonMark\Delimiter\Processor\DelimiterProcessorInterface;
use MvpDocs\Vendor\League\CommonMark\Exception\AlreadyInitializedException;
use MvpDocs\Vendor\League\CommonMark\Extension\ExtensionInterface;
use MvpDocs\Vendor\League\CommonMark\Node\Node;
use MvpDocs\Vendor\League\CommonMark\Parser\Block\BlockStartParserInterface;
use MvpDocs\Vendor\League\CommonMark\Parser\Inline\InlineParserInterface;
use MvpDocs\Vendor\League\CommonMark\Renderer\NodeRendererInterface;
use MvpDocs\Vendor\League\Config\ConfigurationProviderInterface;

/**
 * Interface for building the Environment with any extensions, parsers, listeners, etc. that it may need
 */
interface EnvironmentBuilderInterface extends ConfigurationProviderInterface
{
    /**
     * Registers the given extension with the Environment
     *
     * @throws AlreadyInitializedException if the Environment has already been initialized
     */
    public function addExtension(ExtensionInterface $extension): EnvironmentBuilderInterface;

    /**
     * Registers the given block start parser with the Environment
     *
     * @param BlockStartParserInterface $parser   Block parser instance
     * @param int                       $priority Priority (a higher number will be executed earlier)
     *
     * @return $this
     *
     * @throws AlreadyInitializedException if the Environment has already been initialized
     */
    public function addBlockStartParser(BlockStartParserInterface $parser, int $priority = 0): EnvironmentBuilderInterface;

    /**
     * Registers the given inline parser with the Environment
     *
     * @param InlineParserInterface $parser   Inline parser instance
     * @param int                   $priority Priority (a higher number will be executed earlier)
     *
     * @return $this
     *
     * @throws AlreadyInitializedException if the Environment has already been initialized
     */
    public function addInlineParser(InlineParserInterface $parser, int $priority = 0): EnvironmentBuilderInterface;

    /**
     * Registers the given delimiter processor with the Environment
     *
     * @param DelimiterProcessorInterface $processor Delimiter processors instance
     *
     * @throws AlreadyInitializedException if the Environment has already been initialized
     */
    public function addDelimiterProcessor(DelimiterProcessorInterface $processor): EnvironmentBuilderInterface;

    /**
     * Registers the given node renderer with the Environment
     *
     * @param string                $nodeClass The fully-qualified node element class name the renderer below should handle
     * @param NodeRendererInterface $renderer  The renderer responsible for rendering the type of element given above
     * @param int                   $priority  Priority (a higher number will be executed earlier)
     *
     * @psalm-param class-string<Node> $nodeClass
     *
     * @return $this
     *
     * @throws AlreadyInitializedException if the Environment has already been initialized
     */
    public function addRenderer(string $nodeClass, NodeRendererInterface $renderer, int $priority = 0): EnvironmentBuilderInterface;

    /**
     * Registers the given event listener
     *
     * @param class-string $eventClass Fully-qualified class name of the event this listener should respond to
     * @param callable     $listener   Listener to be executed
     * @param int          $priority   Priority (a higher number will be executed earlier)
     *
     * @return $this
     *
     * @throws AlreadyInitializedException if the Environment has already been initialized
     */
    public function addEventListener(string $eventClass, callable $listener, int $priority = 0): EnvironmentBuilderInterface;
}

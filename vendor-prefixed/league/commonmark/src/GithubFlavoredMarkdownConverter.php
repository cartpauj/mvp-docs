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

namespace MvpDocs\Vendor\League\CommonMark;

use MvpDocs\Vendor\League\CommonMark\Environment\Environment;
use MvpDocs\Vendor\League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use MvpDocs\Vendor\League\CommonMark\Extension\GithubFlavoredMarkdownExtension;

/**
 * Converts GitHub Flavored Markdown to HTML.
 */
final class GithubFlavoredMarkdownConverter extends MarkdownConverter
{
    /**
     * Create a new Markdown converter pre-configured for GFM
     *
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());

        parent::__construct($environment);
    }

    public function getEnvironment(): Environment
    {
        \assert($this->environment instanceof Environment);

        return $this->environment;
    }
}

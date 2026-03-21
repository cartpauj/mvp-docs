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

namespace MvpDocs\Vendor\League\CommonMark\Extension\Embed;

use MvpDocs\Vendor\League\CommonMark\Node\Block\AbstractBlock;

final class Embed extends AbstractBlock
{
    private string $url;
    private ?string $embedCode;

    public function __construct(string $url, ?string $embedCode = null)
    {
        parent::__construct();

        $this->url       = $url;
        $this->embedCode = $embedCode;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getEmbedCode(): ?string
    {
        return $this->embedCode;
    }

    public function setEmbedCode(?string $embedCode): void
    {
        $this->embedCode = $embedCode;
    }
}

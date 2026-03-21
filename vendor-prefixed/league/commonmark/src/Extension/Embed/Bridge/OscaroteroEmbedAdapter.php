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

namespace MvpDocs\Vendor\League\CommonMark\Extension\Embed\Bridge;

use Embed\Embed as EmbedLib;
use MvpDocs\Vendor\League\CommonMark\Exception\MissingDependencyException;
use MvpDocs\Vendor\League\CommonMark\Extension\Embed\Embed;
use MvpDocs\Vendor\League\CommonMark\Extension\Embed\EmbedAdapterInterface;

final class OscaroteroEmbedAdapter implements EmbedAdapterInterface
{
    private EmbedLib $embedLib;

    public function __construct(?EmbedLib $embed = null)
    {
        if ($embed === null) {
            if (! \class_exists(EmbedLib::class)) {
                throw new MissingDependencyException('The embed/embed package is not installed. Please install it with Composer to use this adapter.');
            }

            $embed = new EmbedLib();
        }

        $this->embedLib = $embed;
    }

    /**
     * {@inheritDoc}
     */
    public function updateEmbeds(array $embeds): void
    {
        $extractors = $this->embedLib->getMulti(...\array_map(static fn (Embed $embed) => $embed->getUrl(), $embeds));
        foreach ($extractors as $i => $extractor) {
            if ($extractor->code !== null) {
                $embeds[$i]->setEmbedCode($extractor->code->html);
            }
        }
    }
}

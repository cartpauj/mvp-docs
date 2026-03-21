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

namespace MvpDocs\Vendor\League\CommonMark\Extension\FrontMatter\Data;

use MvpDocs\Vendor\League\CommonMark\Exception\MissingDependencyException;
use MvpDocs\Vendor\League\CommonMark\Extension\FrontMatter\Exception\InvalidFrontMatterException;

final class LibYamlFrontMatterParser implements FrontMatterDataParserInterface
{
    public static function capable(): ?LibYamlFrontMatterParser
    {
        if (! \extension_loaded('yaml')) {
            return null;
        }

        return new LibYamlFrontMatterParser();
    }

    /**
     * {@inheritDoc}
     */
    public function parse(string $frontMatter)
    {
        if (! \extension_loaded('yaml')) {
            throw new MissingDependencyException('Failed to parse yaml: "ext-yaml" extension is missing');
        }

        $result = @\yaml_parse($frontMatter);

        if ($result === false) {
            throw new InvalidFrontMatterException('Failed to parse front matter');
        }

        return $result;
    }
}

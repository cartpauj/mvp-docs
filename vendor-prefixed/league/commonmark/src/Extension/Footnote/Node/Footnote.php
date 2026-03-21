<?php

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 * (c) Rezo Zero / Ambroise Maupate
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Modified by __root__ on 21-March-2026 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace MvpDocs\Vendor\League\CommonMark\Extension\Footnote\Node;

use MvpDocs\Vendor\League\CommonMark\Node\Block\AbstractBlock;
use MvpDocs\Vendor\League\CommonMark\Reference\ReferenceInterface;
use MvpDocs\Vendor\League\CommonMark\Reference\ReferenceableInterface;

final class Footnote extends AbstractBlock implements ReferenceableInterface
{
    /** @psalm-readonly */
    private ReferenceInterface $reference;

    public function __construct(ReferenceInterface $reference)
    {
        parent::__construct();

        $this->reference = $reference;
    }

    public function getReference(): ReferenceInterface
    {
        return $this->reference;
    }
}

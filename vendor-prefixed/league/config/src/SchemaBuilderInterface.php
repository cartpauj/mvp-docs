<?php
/**
 * @license BSD-3-Clause
 *
 * Modified by __root__ on 21-March-2026 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

/*
 * This file is part of the league/config package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MvpDocs\Vendor\League\Config;

use MvpDocs\Vendor\Nette\Schema\Schema;

/**
 * Interface that allows new schemas to be added to a configuration
 */
interface SchemaBuilderInterface
{
    /**
     * Registers a new configuration schema at the given top-level key
     */
    public function addSchema(string $key, Schema $schema): void;
}

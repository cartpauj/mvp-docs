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

namespace MvpDocs\Vendor\League\CommonMark\Node\Query;

use MvpDocs\Vendor\League\CommonMark\Node\Node;

/**
 * @internal
 */
final class AndExpr implements ExpressionInterface
{
    /**
     * @var callable[]
     * @psalm-var list<callable(Node): bool>
     */
    private array $conditions;

    /**
     * @psalm-param callable(Node): bool $expressions
     */
    public function __construct(callable ...$expressions)
    {
        $this->conditions = \array_values($expressions);
    }

    /**
     * @param callable(Node): bool $expression
     */
    public function add(callable $expression): void
    {
        $this->conditions[] = $expression;
    }

    public function __invoke(Node $node): bool
    {
        foreach ($this->conditions as $condition) {
            if (! $condition($node)) {
                return false;
            }
        }

        return true;
    }
}

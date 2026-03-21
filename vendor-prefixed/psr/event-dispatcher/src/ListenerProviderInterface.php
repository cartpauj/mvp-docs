<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 21-March-2026 using {@see https://github.com/BrianHenryIE/strauss}.
 */
declare(strict_types=1);

namespace MvpDocs\Vendor\Psr\EventDispatcher;

/**
 * Mapper from an event to the listeners that are applicable to that event.
 */
interface ListenerProviderInterface
{
    /**
     * @param object $event
     *   An event for which to return the relevant listeners.
     * @return iterable[callable]
     *   An iterable (array, iterator, or generator) of callables.  Each
     *   callable MUST be type-compatible with $event.
     */
    public function getListenersForEvent(object $event) : iterable;
}

<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 21-March-2026 using {@see https://github.com/BrianHenryIE/strauss}.
 */
declare(strict_types=1);

namespace MvpDocs\Vendor\Psr\EventDispatcher;

/**
 * Defines a dispatcher for events.
 */
interface EventDispatcherInterface
{
    /**
     * Provide all relevant listeners with an event to process.
     *
     * @param object $event
     *   The object to process.
     *
     * @return object
     *   The Event that was passed, now modified by listeners.
     */
    public function dispatch(object $event);
}

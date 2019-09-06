<?php
namespace Keepper\EventListener\Contract;

/**
 * Interface ListenerProviderInterface
 * @package Keepper\EventListener\Contract
 * @author Andrew Kosov <andrew.kosov@gmail.com>
 */
interface ListenerProviderInterface {

	/**
	 * Возвращает слушателей, для указанного события
	 * @param string $eventId
	 * @return callable[]
	 */
	public function getListeners(string $eventId): array;

	/**
	 * Возвращает признак наличия слушателей указанного события
	 * @param string $eventId
	 * @return bool
	 */
	public function hasListeners(string $eventId): bool;

}
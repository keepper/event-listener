<?php
namespace Keepper\EventListener\Contract;

/**
 * Interface ListenerManagerInterface
 * @package Keepper\EventListener\Manager
 * @author Andrew Kosov <andrew.kosov@gmail.com>
 */
interface ListenerManagerInterface extends ListenerProviderInterface {

	/**
	 * Добавляет слушателя, как екземпляр класса реализующего конкретный интерфейс "Слушателя события"
	 * @param ListenerInterface $listener
	 * @throws UnknownListenerException
	 */
	public function addListener(ListenerInterface $listener);

	/**
	 * Удаляет слушателя.
	 * @throws UnregisteredListenerException - В случае если указанный слушатель, не был подписан
	 */
	public function removeListener(ListenerInterface $listener): void;
}
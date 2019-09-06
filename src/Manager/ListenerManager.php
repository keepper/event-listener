<?php
namespace Keepper\EventListener\Manager;

use Keepper\EventListener\Contract\ListenerInterface;
use Keepper\EventListener\Contract\ListenerManagerInterface;
use Keepper\EventListener\Contract\UnknownListenerException;
use Keepper\EventListener\Contract\UnregisteredListenerException;
use Keepper\EventListener\Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class ListenerManager implements ListenerManagerInterface, LoggerAwareInterface {

	use LoggerAwareTrait;

	private $metaData = [];

	private $listeners = [];

	public function __construct() {
		$this->setLogger(new NullLogger());
	}

	/**
	 * Регистрирует интерфейс слушателя события
	 * @param string $listenerInterfaceName
	 * @throws Exception
	 */
	public function addListenerInterface(string $listenerInterfaceName) {
		if ( array_key_exists($listenerInterfaceName, $this->metaData) ) {
			$this->logErrorAndThrow('Указанный интерфейс слушателя "%s" уже ранее был зарегистрирован', [$listenerInterfaceName]);
		}

		if ( !$this->isInterfaceExists($listenerInterfaceName) ) {
			$this->logErrorAndThrow('Указанный интерфейс слушателя "%s" не найден', [$listenerInterfaceName]);
		}

		try {
			$reflection = new \ReflectionClass($listenerInterfaceName);
			$methods = $reflection->getMethods();
		} catch (\ReflectionException $e) {
			$this->logErrorAndThrow('Указанный интерфейс слушателя "%s" не корректен. Ошибка анализа через Reflection: %s', [$listenerInterfaceName, $e->getMessage()]);
		}

		if ( !$reflection->implementsInterface(ListenerInterface::class) ) {
			$this->logErrorAndThrow('Указанный интерфейс слушателя "%s" не наследует маркерный интерфейс ListenerInterface', [$listenerInterfaceName]);
		}

		if (count($methods) != 1) {
			$this->logErrorAndThrow('Указанный интерфейс слушателя "%s" не соответствует контракту и содержит не корректное количество описываемых методов', [$listenerInterfaceName]);
		}

		$methodName = $methods[0]->name;

		$this->metaData[$listenerInterfaceName] = $methodName;
	}

	protected function isInterfaceExists(string $interfaceName): bool {
		return interface_exists($interfaceName);
	}

	public function getListenerMethodName(string $listenerInterfaceName): ?string {
		if ( !array_key_exists($listenerInterfaceName, $this->metaData) ) {
			return null;
		}

		return $this->metaData[$listenerInterfaceName];
	}

	private function logErrorAndThrow(string $message, $parameters = [], $className = Exception::class) {
		$message = sprintf($message, ...$parameters);
		$this->logger->error($message);
		throw new $className($message);
	}

	/**
	 * Добавяле слушателя как callable
	 * @param string $eventId
	 * @param callable $handler
	 * @throws Exception
	 */
	public function addHandler(string $eventId, callable $handler) {
		if ( !array_key_exists($eventId, $this->metaData) ) {
			$this->logErrorAndThrow('Указанный идентификатор события "%s" не известен', [$eventId], UnknownListenerException::class);
		}

		if ( !array_key_exists($eventId, $this->listeners) ) {
			$this->listeners[$eventId] = [];
		}

		$this->listeners[$eventId][] = $handler;
	}

	/**
	 * @inheritdoc
	 */
	public function addListener(ListenerInterface $listener) {
		$knownInterfaces = false;
		foreach ($this->metaData as $interfaceName => $methodName) {
			if ( !($listener instanceof $interfaceName) ) {
				continue;
			}

			$knownInterfaces = true;
			$this->addHandler($interfaceName, [$listener, $methodName]);
		}

		if (!$knownInterfaces) {
			$this->logErrorAndThrow('Указанный слушатель не реализует ни один известный интерфейс слушателя событий', [], UnknownListenerException::class);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function removeListener(ListenerInterface $listener): void {
		$knownInterfaces = false;
		foreach ($this->metaData as $interfaceName => $methodName) {
			if ( !($listener instanceof $interfaceName) ) {
				continue;
			}

			if ( !$this->hasListeners($interfaceName) ) {
				continue;
			}

			if ( !in_array([$listener, $methodName], $this->listeners[$interfaceName]) ){
				continue;
			}

			$tmp = [];
			foreach ($this->listeners[$interfaceName] as $handler) {
				if ($handler == [$listener, $methodName]) {
					$knownInterfaces = true;
					continue;
				}

				$tmp[] = $handler;
			}
			$this->listeners[$interfaceName] = $tmp;
		}

		if (!$knownInterfaces) {
			$this->logErrorAndThrow('Указанный слушатель не был ранее подписан', [], UnregisteredListenerException::class);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getListeners(string $eventId): array {
		if (!$this->hasListeners($eventId) ) {
			return [];
		}

		return $this->listeners[$eventId];
	}

	/**
	 * @inheritdoc
	 */
	public function hasListeners(string $eventId): bool {
		return array_key_exists($eventId, $this->listeners) && count($this->listeners[$eventId]) > 0;
	}
}
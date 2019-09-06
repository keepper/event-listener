<?php

namespace Keepper\EventListener\Dispatcher;

class StoppableDispatcher extends AbstractDispatcher {

	public function dispatch(string $eventId, ...$arguments) {
		if (!$this->listenerProvider()->hasListeners($eventId)) {
			$this->logger->debug('Для события "' . $eventId . '" отсутствуют слушатели');

			return;
		}

		$listeners = $this->listenerProvider()->getListeners($eventId);
		$controll = new PropagationControll();
		$arguments[] = &$controll;

		foreach ($listeners as $listener) {

			$listener(...$arguments);

			if ( $controll->isStopped() ) {
				$this->logger->debug('Распространение события "' . $eventId . '" остановлено');
				return;
			}

		}
	}
}
<?php
namespace Keepper\EventListener\Dispatcher;

class NotificationDispatcher extends AbstractDispatcher {

	public function dispatch(string $eventId, ...$arguments) {
		if (!$this->listenerProvider()->hasListeners($eventId)) {
			$this->logger->debug('Для события "' . $eventId . '" отсутствуют слушатели');

			return;
		}

		$listeners = $this->listenerProvider()->getListeners($eventId);

		foreach ($listeners as $listener) {
			try {

				$listener(...$arguments);

			} catch (StopPropagationException $e) {

				$this->logger->error('При вызове слушателя события "' . $eventId . '" произошло исключение (останавливающее распространение события). ' . $e->getMessage());
				$this->logger->debug($e->getTraceAsString());
				throw $e;

			} catch (\Exception $e) {

				$this->logger->error('При вызове слушателя события "' . $eventId . '" произошло исключение. ' . $e->getMessage());
				$this->logger->debug($e->getTraceAsString());
			}

		}
	}
}
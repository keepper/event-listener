<?php

namespace Keepper\EventListener\Dispatcher;


use Keepper\EventListener\Contract\ListenerProviderInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

abstract class AbstractDispatcher implements LoggerAwareInterface {

	use LoggerAwareTrait;

	/**
	 * @var ListenerProviderInterface
	 */
	private $listenerProvider;

	public function __construct(
		ListenerProviderInterface $listenerProvider
	) {
		$this->listenerProvider = $listenerProvider;
		$this->setLogger(new NullLogger());
	}

	protected function listenerProvider(): ListenerProviderInterface {
		return $this->listenerProvider;
	}

	abstract public function dispatch(string $eventId, ...$arguments);
}
<?php
namespace Keepper\EventListener\Tests\Dispatcher;

use Keepper\EventListener\Contract\ListenerProviderInterface;
use Keepper\EventListener\Dispatcher\NotificationDispatcher;
use Keepper\EventListener\Dispatcher\StopPropagationException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class NotificationDispatcherTest extends TestCase {

	public function testDispatchWithoutListeners() {
		$someEventId = 'some-event-id';

		$logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
		$logger->expects($this->once())->method('debug')->with('Для события "' . $someEventId . '" отсутствуют слушатели');

		$listenerProvider = $this->getMockBuilder(ListenerProviderInterface::class)->getMock();
		$listenerProvider
			->method('hasListeners')
			->with($someEventId)
			->willReturn(false);

		$listenerProvider
			->method('getListeners')
			->with($someEventId)
			->willReturn([]);

		$dispatcher = new NotificationDispatcher($listenerProvider);
		$dispatcher->setLogger($logger);
		$dispatcher->dispatch($someEventId);
	}

	public function testDispatchPositive() {
		$someEventId = 'some-event-id';

		$firstRuned = false;
		$secondRuned = false;

		$listenerProvider = $this->getMockBuilder(ListenerProviderInterface::class)->getMock();
		$listenerProvider
			->method('hasListeners')
			->with($someEventId)
			->willReturn(true);

		$listenerProvider
			->method('getListeners')
			->with($someEventId)
			->willReturn([
				function () use (&$firstRuned) {
					$firstRuned = true;
				},
				function () use (&$secondRuned) {
					$secondRuned = true;
				},
			]);

		$dispatcher = new NotificationDispatcher($listenerProvider);
		$dispatcher->dispatch($someEventId);

		$this->assertTrue($firstRuned);
		$this->assertTrue($secondRuned);
	}

	public function testDispatchWithIgnoringExceptionOnListener() {
		$someEventId = 'some-event-id';

		$logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
		$logger->expects($this->once())->method('error');

		$firstRuned = false;
		$secondRuned = false;

		$listenerProvider = $this->getMockBuilder(ListenerProviderInterface::class)->getMock();
		$listenerProvider
			->method('hasListeners')
			->with($someEventId)
			->willReturn(true);

		$listenerProvider
			->method('getListeners')
			->with($someEventId)
			->willReturn([
				function () use (&$firstRuned) {
					$firstRuned = true;
					throw new \Exception('Some error on listener');
				},
				function () use (&$secondRuned) {
					$secondRuned = true;
				},
			]);

		$dispatcher = new NotificationDispatcher($listenerProvider);
		$dispatcher->setLogger($logger);
		$dispatcher->dispatch($someEventId);

		$this->assertTrue($firstRuned);
		$this->assertTrue($secondRuned);
	}

	public function testDispatchWithStopExceptionOnListener() {
		$someEventId = 'some-event-id';

		$logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
		$logger->expects($this->once())->method('error');

		$firstRuned = false;
		$secondRuned = false;

		$listenerProvider = $this->getMockBuilder(ListenerProviderInterface::class)->getMock();
		$listenerProvider
			->method('hasListeners')
			->with($someEventId)
			->willReturn(true);

		$listenerProvider
			->method('getListeners')
			->with($someEventId)
			->willReturn([
				function () use (&$firstRuned) {
					$firstRuned = true;
					throw new StopPropagationException('Some stop propagation error on listener');
				},
				function () use (&$secondRuned) {
					$secondRuned = true;
				},
			]);

		$this->expectException(StopPropagationException::class);

		$dispatcher = new NotificationDispatcher($listenerProvider);
		$dispatcher->setLogger($logger);
		$dispatcher->dispatch($someEventId);

		$this->assertTrue($firstRuned);
		$this->assertFalse($secondRuned);
	}
}
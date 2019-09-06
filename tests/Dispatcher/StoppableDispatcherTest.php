<?php

namespace Keepper\EventListener\Tests\Dispatcher;

use Keepper\EventListener\Contract\ListenerProviderInterface;
use Keepper\EventListener\Dispatcher\PropagationControll;
use Keepper\EventListener\Dispatcher\StoppableDispatcher;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class StoppableDispatcherTest extends TestCase {

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

		$dispatcher = new StoppableDispatcher($listenerProvider);
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
				function (...$arguments) use (&$firstRuned) {
					$firstRuned = true;
					$this->assertCount(2, $arguments);
					$this->assertEquals('someArgument', $arguments[0]);
					$this->assertInstanceOf(PropagationControll::class, $arguments[1]);
				},
				function (...$arguments) use (&$secondRuned) {
					$secondRuned = true;
					$this->assertCount(2, $arguments);
					$this->assertEquals('someArgument', $arguments[0]);
					$this->assertInstanceOf(PropagationControll::class, $arguments[1]);
				},
			]);

		$dispatcher = new StoppableDispatcher($listenerProvider);
		$dispatcher->dispatch($someEventId, 'someArgument');

		$this->assertTrue($firstRuned);
		$this->assertTrue($secondRuned);
	}

	public function testStopPropagatyion() {
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
				function (...$arguments) use (&$firstRuned) {
					$firstRuned = true;
					$this->assertCount(2, $arguments);
					$this->assertEquals('someArgument', $arguments[0]);
					$this->assertInstanceOf(PropagationControll::class, $arguments[1]);
					$arguments[1]->stopPropagation();
				},
				function (...$arguments) use (&$secondRuned) {
					$secondRuned = true;
				},
			]);

		$dispatcher = new StoppableDispatcher($listenerProvider);
		$dispatcher->dispatch($someEventId, 'someArgument');

		$this->assertTrue($firstRuned);
		$this->assertFalse($secondRuned);
	}
}
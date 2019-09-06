<?php
namespace Keepper\EventListener\Tests\Manager;

use Keepper\EventListener\Contract\ListenerInterface;
use Keepper\EventListener\Exception;
use Keepper\EventListener\Manager\ListenerManager;
use Keepper\EventListener\Tests\Fixtures\SomeIncorrectListenerInterface;
use Keepper\EventListener\Tests\Fixtures\SomeListenerInterface;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class ListenerManagerTest extends TestCase {

	/**
	 * @var ListenerManager
	 */
	private $manager;

	public function setUp() {
		parent::setUp();
		$this->manager = new ListenerManager();
		$this->manager->setLogger(new Logger('UnitTest'));
	}

	/**
	 * @dataProvider dataProviderForAddListenerInterfaceNameNegative
	 */
	public function testAddListenerInterfaceNameNegative($interfaceName, $expectedMessage, $mockInterfaceExists = false) {

		if ( $mockInterfaceExists ) {
			$this->manager = $this->getMockBuilder(ListenerManager::class)->setMethods(['isInterfaceExists'])->getMock();
			$this->manager->setLogger(new Logger('UnitTest'));

			$this->manager->method('isInterfaceExists')->willReturn(true);
		}

		$this->manager->addListenerInterface(SomeListenerInterface::class);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage($expectedMessage);

		$this->manager->addListenerInterface($interfaceName);
	}

	public function dataProviderForAddListenerInterfaceNameNegative() {
		return [
			[SomeListenerInterface::class, 'Указанный интерфейс слушателя "'.SomeListenerInterface::class.'" уже ранее был зарегистрирован'],
			['SomeInterface', 'Указанный интерфейс слушателя "SomeInterface" не найден'],
			[\DateTimeInterface::class, 'Указанный интерфейс слушателя "'.\DateTimeInterface::class.'" не наследует маркерный интерфейс ListenerInterface'],
			[SomeIncorrectListenerInterface::class, 'Указанный интерфейс слушателя "'.SomeIncorrectListenerInterface::class.'" не соответствует контракту и содержит не корректное количество описываемых методов'],
			['SomeInterface', 'Указанный интерфейс слушателя "SomeInterface" не корректен. Ошибка анализа через Reflection: Class SomeInterface does not exist', true]
		];
	}

	public function testAddListenerInterfaceNamePositive() {

		$this->manager->addListenerInterface(SomeListenerInterface::class);

		$this->assertEquals('onSomeEvent', $this->manager->getListenerMethodName(SomeListenerInterface::class));
	}

	public function testGetListenerMethodName() {

		$this->assertNull($this->manager->getListenerMethodName(SomeListenerInterface::class));

		$this->manager->addListenerInterface(SomeListenerInterface::class);

		$this->assertEquals('onSomeEvent', $this->manager->getListenerMethodName(SomeListenerInterface::class));
	}

	/**
	 * @expectedException  \Keepper\EventListener\Contract\UnknownListenerException
	 */
	public function testAddHandlerForUnregisteredEventId() {
		$this->manager->addHandler('someUnregisteredEvent', function (){});
	}

	public function testAddHandler() {
		$this->manager->addListenerInterface(SomeListenerInterface::class);
		$handler = function(){};
		$this->manager->addHandler(SomeListenerInterface::class, $handler);

		$this->assertEquals([$handler], $this->manager->getListeners(SomeListenerInterface::class));
	}

	/**
	 * @expectedException \Keepper\EventListener\Contract\UnknownListenerException
	 */
	public function testAddListenerNegative() {
		$this->manager->addListenerInterface(SomeListenerInterface::class);

		$listener = new class implements ListenerInterface{

		};

		$this->manager->addListener($listener);
	}

	public function testAddListenerPositive() {
		$this->manager->addListenerInterface(SomeListenerInterface::class);

		$listener = new class implements SomeListenerInterface {

			public function onSomeEvent() {

			}
		};

		$this->manager->addListener($listener);

		$this->assertEquals([[$listener, 'onSomeEvent']], $this->manager->getListeners(SomeListenerInterface::class));
	}

	/**
	 * @dataProvider dataProviderForRemoveListenerNegative
	 * @expectedException \Keepper\EventListener\Contract\UnregisteredListenerException
	 */
	public function testRemoveListenerNegative($listener, $addListener = true) {
		$this->manager->addListenerInterface(SomeListenerInterface::class);
		if ($addListener) {
			$this->manager->addListener(new class implements SomeListenerInterface {

				public function onSomeEvent() {

				}
			});
		}

		$this->manager->removeListener($listener);
	}

	public function dataProviderForRemoveListenerNegative() {
		return [
			[new class implements ListenerInterface{}],
			[new class implements SomeListenerInterface {

				public function onSomeEvent() {

				}
			}],
			[new class implements SomeListenerInterface {

				public function onSomeEvent() {

				}
			}, false],
		];
	}

	public function testRemoveListenerPositive() {
		$this->manager->addListenerInterface(SomeListenerInterface::class);

		$listenerOne = new class implements SomeListenerInterface {

			public function onSomeEvent() {

			}
		};

		$listenerTwo = new class implements SomeListenerInterface {

			public function onSomeEvent() {

			}
		};

		$this->manager->addListener($listenerOne);
		$this->manager->addListener($listenerTwo);

		$this->assertEquals([[$listenerOne, 'onSomeEvent'], [$listenerTwo, 'onSomeEvent']], $this->manager->getListeners(SomeListenerInterface::class));

		$this->manager->removeListener($listenerOne);

		$this->assertEquals([[$listenerTwo, 'onSomeEvent']], $this->manager->getListeners(SomeListenerInterface::class));
	}

	public function testGetListenersWithoutListeners() {
		$this->assertCount(0, $this->manager->getListeners(SomeListenerInterface::class));
	}
}
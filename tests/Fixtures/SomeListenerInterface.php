<?php
namespace Keepper\EventListener\Tests\Fixtures;

use Keepper\EventListener\Contract\ListenerInterface;

interface SomeListenerInterface extends ListenerInterface {

	public function onSomeEvent();
}
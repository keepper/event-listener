<?php
namespace Keepper\EventListener\Dispatcher;

class PropagationControll {

	private $isStopped = false;

	public function stopPropagation() {
		$this->isStopped = true;
	}

	public function isStopped(): bool {
		return $this->isStopped;
	}
}
<?php

namespace Keepper\EventListener\Contract;

use Keepper\EventListener\Exception;

/**
 * Class UnregisteredListenerException
 * @package Keepper\EventListener\Contract
 *
 * @author Andrew Kosov <andrew.kosov@gmail.com>
 *
 * Исключение генерируемое при попытке удалить слушателя, который ранее не был зарегистрирован
 */
class UnregisteredListenerException extends Exception {

}
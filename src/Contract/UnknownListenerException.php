<?php

namespace Keepper\EventListener\Contract;

use Keepper\EventListener\Exception;

/**
 * Class UnknownListenerException
 * @package Keepper\EventListener\Manager
 * @author Andrew Kosov <andrew.kosov@gmail.com>
 *
 * Исключение генерируемое при попытке зарегистрирован слушателя, мета данные которого не известны
 */
class UnknownListenerException extends Exception {

}
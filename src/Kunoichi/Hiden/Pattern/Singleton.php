<?php

namespace Kunoichi\Hiden\Pattern;

/**
 * Singleton pattern.
 *
 * @package hiden
 */
abstract class Singleton {

	/**
	 * @var static[]
	 */
	private static $instances = [];

	/**
	 * Constructor
	 */
	final private function __construct() {
		$this->init();
	}

	/**
	 * Called inside constructor.
	 */
	protected function init() {
		// Do something inside.
	}

	/**
	 * Get instance.
	 *
	 * @return static
	 */
	public static function get_instance() {
		$class_name = get_called_class();
		if ( ! isset( self::$instances[ $class_name ] ) ) {
			self::$instances[ $class_name ] = new $class_name();
		}
		return self::$instances[ $class_name ];
	}
}

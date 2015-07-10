<?php
/**
 * mirocow\yandexmaps\Map class file.
 */

namespace esoftkz\yandexmaps;

use yii;
use yii\base\Component;
use yii\base\Exception;


use esoftkz\yandexmaps\Interfaces;

/**
 * @property string $id
 * @property array $objects
 * @property array $controls
 */
class Map extends Component implements Interfaces\EventAggregate{

	/** @var array */
	public $state = array();
	/** @var array */
	public $options = array();

	/** @var string */
	private $_id;
	/** @var array */
	private $_objects = array();
	/** @var array */
	private $_controls = array();
	/** @var array */
	private $_events = array();
	/** @var array */
	private $_behaviors = array();

	/**
	 * @param string $id
	 * @param array $state
	 * @param array $options
	 */
	public function __construct($id = 'yandexMap', array $state = array(),
	  array $options = array()) {

		$this->setId($id);
		$this->state = $state;

		if (isset($options['controls'])) {
			$this->setControls($options['controls']);
			unset($options['controls']);
		}

		if (isset($options['events'])) {
			$this->setEvents($options['events']);
			unset($options['events']);
		}

		if (isset($options['objects'])) {
			$this->setObjects($options['objects']);			
			unset($options['objects']);
		}

		if (isset($options['behaviors'])) {
			$this->setBehaviors($options['behaviors']);
			unset($options['behaviors']);
		}

		$this->options = $options;

	}

	/**
	 * Clone object.
	 */
	function __clone() {
		$this->id = null;
	}

	/**
	 * @param string $code
	 * @throws Exception
	 */
	final public function setCode($code) {
		throw new Exception('Cannot change code directly.');
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	public function getId() {
		if (empty($this->_id)) {
			throw new Exception('Empty map ID.');
		}

		return $this->_id;
	}

	/**
	 * @param string $id
	 */
	public function setId($id) {
		$this->_id = (string) $id;
	}

	/**
	 * @param array $events
	 */
	public function setEvents(array $events) {
		$this->_events = $events;
	}

	/**
	 * @return array
	 */
	public function getEvents() {
		return $this->_events;
	}

	/**
	 * @param array $behaviors
	 */
	public function setBehaviors(array $behaviors) {
		$this->_behaviors = $behaviors;
	}

	/**
	 * @return array
	 */
	public function getBehaviors() {
		return $this->_behaviors;
	}

	/**
	 * @return array
	 */
	public function getObjects() {
		return $this->_objects;
	}

	/**
	 * @param array $objects
	 */
	public function setObjects(array $objects = array()) {
		$this->_objects = array();
		foreach ($objects as $object) {
			$this->addObject($object);
		}
	}

	/**
	 * @param mixed $object
	 * @return Map
	 */
	public function addObject($object) {
		$this->_objects[] = $object;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getControls() {
		return $this->_controls;
	}

	/**
	 * @param array $controls
	 */
	public function setControls(array $controls) {
		$this->_controls = array();
		foreach ($controls as $control) {
			$this->addControl($control);
		}
	}

	/**
	 * The control.
	 */
	public function addControl($control) {
		if (is_string($control)) {
			$control = array($control);
		} elseif (is_array($control) && (!isset($control[0]) || !is_string($control[0]))) {
			throw new Exception('Invalid control.');
		}
		$this->_controls[$control[0]] = $control;

		return $this;
	}
}
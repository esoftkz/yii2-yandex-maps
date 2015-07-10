<?php

namespace esoftkz\yandexmaps\objects;

use esoftkz\yandexmaps\GeoObject;

use esoftkz\yandexmaps\Interfaces;
/**
 * Placemark
 */
class Placemark  extends GeoObject implements Interfaces\EventAggregate{

	/** @var array */
	private $_events = array();
	
	public function __construct(array $geometry, array $properties = array(), array $options = array()) {
		if (isset($options['events'])) {
			$this->setEvents($options['events']);
			unset($options['events']);
		}
		
		$feature = array(
		  'geometry' => array(
			'type' => "Point",
			'coordinates' => $geometry,
		  ),
		  'properties' => $properties,
		);
		parent::__construct($feature, $options);
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
	 * @return array
	 */
	public function getGeometry() {
		$geometry = parent::getGeometry();
		if (isset($geometry['coordinates'])) {
			$geometry = $geometry['coordinates'];
		}

		return $geometry;
	}
	
}
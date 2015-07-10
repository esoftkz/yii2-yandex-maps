<?php
/**
 * Api class file.
 */

namespace esoftkz\yandexmaps;

use yii;
use yii\base\Component;
use yii\helpers\Json;
use yii\web\View;


/**
 * Yandex Maps API component.
 */
class Api extends Component {
	const SCRIPT_ID = 'yandex.maps.api';

	/** @var string */
	public $protocol = 'http';

	/** @var string */
	public $uri = 'api-maps.yandex.ru';

	/** @var string */
	public $api_version = '2.1-dev';

	/** @var string */
	public $language = 'ru-RU';
	/** @var array */
	public $packages = array('package.full');

	/** @var array */
	private $_objects = array();

	/**
	 * @param mixed $key
	 * @param mixed $object
	 * @return $this
	 */
	public function addObject($object, $key = null) {
		if (null === $key) {
			$this->_objects[] = $object;
		} else {
			$this->_objects[$key] = $object;
		}
		return $this;
	}
	
	protected function encodeArray($array) {
		return count($array) > 0 ? Json::encode($array) : '{}';
	}
	
	/**
	 * Render client scripts.
	 */
	public function render() {
		$this->registerScriptFile();
		$this->registerScript();
	}


	protected function registerScriptFile() {
		if ('https' !== $this->protocol) {
			$this->protocol = 'http';
		}

		if (is_array($this->packages)) {
			$this->packages = implode(',', $this->packages);
		}

		$url = $this->protocol . '://' . $this->uri . '/' . $this->api_version . '/?lang=' . $this->language . '&load=' . $this->packages;

		Yii::$app->view->registerJsFile($url, ['position' => View::POS_END]);
	}

	/**
	 * Register client script.
	 */
	protected function registerScript() {
		$js = "\$Maps = []; var placemark;\nymaps.ready(function() {\n";	
		foreach ($this->_objects as $var => $object) {			
			$js .= $this->generateObject($object, $var) . "\n";
			
		}		
		$js .= "});\n";		
		
		Yii::$app->view->registerJs($js, View::POS_READY, self::SCRIPT_ID);
	}

	
	public function generateObject($object, $var = null) {
		$class = get_class($object);
		$class = substr($class, strrpos($class, '\\') + 1);
		$generator = 'generate' . $class;
		
		if (method_exists($this, $generator)) {
			$var = is_numeric($var) ? null : $var;
			$js = $this->$generator($object, $var);
			
			
			if (count($object->getEvents()) > 0) {				
				if (null !== $var && $class!='Placemark') {
					$events = "\n\$Maps['$var'].events";
					foreach ($object->getEvents() as $event => $handle) {			
						$event = Json::encode($event);						
						$events .= "\n.add($event, $handle)";
					}
					$js .= "$events;\n";					
				}elseif(null !== $var && $class=='Placemark'){
					$js .= ");\n";
					$events = "\n\$Maps['$var'].geoObjects.events";
					foreach ($object->getEvents() as $event => $handle) {			
						$event = Json::encode($event);						
						$events .= "\n.add($event, $handle";
					}
					$js .= "$events\n";
				}
			}
			
			
		} else {
			$js = Json::encode($object);
		}
		

		return $js;
	}
	
	public function generateMap(Map $map, $var = null) {
		$id = $map->id;
		$state = $this->encodeArray($map->state);
		$options = $this->encodeArray($map->options);
		$js = "new ymaps.Map('$id', $state, $options)";	
		
		if (null !== $var) {
			$js = "\$Maps['$var'] = $js;\n";
			
			if (count($map->objects) > 0) {				
				$objects = '';
						
				foreach ($map->objects as $i => $object) {
					if (!$object) {
						continue;
					}					
					if ($object instanceof GeoObject) {
						$_object = $this->generateObject($object, $var); 					
						$objects .= ".add($_object)\n"; 	
					}elseif (is_string($object)) {
						$js .= "$object;\n";
					}
				}
				if (!empty($objects)) {
					$js .= "\n\$Maps['$id'].geoObjects$objects;\n";
				}
			}
			
			if (count($map->controls) > 0) {
				$controls = "\n\$Maps['$id'].controls";
				foreach ($map->controls as $control) {
					$controls .= "\n\t.add($control[0])";
				}
				$js .= "$controls;\n";
			}
			
		}
		
		return $js;
	}
	
	public function generatePlacemark(objects\Placemark $object, $var = null) {
		
		$geometry = Json::encode($object->geometry);
		
		$properties = $this->encodeArray($object->properties);
		$options = $this->encodeArray($object->options);
		//Вот эта шняга работает только для одного placemark - нажуен массив (лень)
		$js = "placemark = new ymaps.Placemark($geometry, $properties, $options)";
		
		
		
		
		return $js;
	}
	
	
	
	
	
	
}
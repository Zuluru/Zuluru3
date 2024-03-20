<?php
namespace App\Core;

use Cake\Controller\Controller;
use Cake\Core\App;
use Cake\Core\ObjectRegistry;
use Cake\Event\EventDispatcherInterface;
use Cake\Event\EventDispatcherTrait;
use Cake\Event\EventListenerInterface;
use Cake\Utility\Inflector;
use App\Exception\MissingModuleException;

/**
 * ModuleRegistry is a registry for loaded modules
 *
 * Handles loading, constructing and binding events for module objects.
 */
class ModuleRegistry extends ObjectRegistry implements EventDispatcherInterface {

	use EventDispatcherTrait;

	/**
	 * The instance of the registry.
	 *
	 * @var \App\Core\ModuleRegistry
	 */
	private static $instance = null;

	/**
	 * The controller that this registry was initialized with.
	 *
	 * @var \Cake\Controller\Controller
	 */
	protected $_Controller = null;

	/**
	 * Constructor.
	 *
	 * @param \Cake\Controller\Controller|null $controller Controller instance.
	 */
	public function __construct(Controller $controller = null) {
		self::$instance =& $this;
		if ($controller) {
			$this->setController($controller);
		}
	}

	/**
	 * Set the controller associated with the collection.
	 *
	 * @param \Cake\Controller\Controller $controller Controller instance.
	 * @return void
	 */
	public function setController(Controller $controller) {
		$this->_Controller = $controller;
		$this->setEventManager($controller->getEventManager());
	}

	public static function &getInstance() {
		if (!self::$instance) {
			new ModuleRegistry();
		}
		return self::$instance;
	}

	public static function getModuleList($type) {
		$files = glob(APP . 'Module' . DS . $type . '?*.php');
		$ret = [];
		foreach ($files as $file) {
			$file = explode(DS, $file);
			$file = explode('.', array_pop($file));
			$file = array_shift($file);
			$class = substr($file, strlen($type));
			$ret[] = Inflector::underscore($class);
		}

		return $ret;
	}

	/**
	 * Resolve a module classname.
	 *
	 * Part of the template method for Cake\Core\ObjectRegistry::load()
	 *
	 * @param string $class Partial classname to resolve.
	 * @return string|false Either the correct classname or false.
	 */
	protected function _resolveClassName(string $class): ?string {
		list($class, $type) = $this->moduleSplit($class);
		return App::className($class, 'Module', $type);
	}

	/**
	 * Throws an exception when a module is missing.
	 *
	 * Part of the template method for Cake\Core\ObjectRegistry::load()
	 *
	 * @param string $class The classname that is missing.
	 * @param string $plugin The plugin the module is missing in.
	 * @return void
	 * @throws \App\Exception\MissingModuleException
	 */
	protected function _throwMissingClassError(string $class, ?string $plugin): void {
		list($class, $type) = $this->moduleSplit($class);
		throw new MissingModuleException([
			'class' => $class . $type,
			'plugin' => $plugin
		]);
	}

	/**
	 * Create the module instance.
	 *
	 * Part of the template method for Cake\Core\ObjectRegistry::load()
	 * Enabled modules will be registered with the event manager.
	 *
	 * @param string $class The classname to create.
	 * @param string $alias The alias of the module.
	 * @param array $config An array of config to use for the module.
	 * @return mixed The constructed module class.
	 */
	protected function _create($class, $alias, $config) {
		$instance = new $class($this, $config);
		$enable = isset($config['enabled']) ? $config['enabled'] : true;
		if ($enable && $instance instanceof EventListenerInterface) {
			$this->getEventManager()->on($instance);
		}
		return $instance;
	}

	private function moduleSplit($name)	{
		if (strpos($name, ':') !== false) {
			$parts = explode(':', $name, 2);
			$parts[1] = Inflector::camelize(strtolower($parts[1]));
			return $parts;
		}
		return [$name, ''];
	}

}

<?php
namespace App\Policy;

use Authorization\Policy\Exception\MissingPolicyException;
use Authorization\Policy\OrmResolver;

class TypeResolver extends OrmResolver {

	protected $_manglers;
	protected $_plugin = null;

	/**
	 * Constructor
	 *
	 * @param string $appNamespace The application namespace
	 * @param array<string, string> $overrides A list of plugin name overrides.
	 */
	public function __construct($manglers, $appNamespace = 'App', array $overrides = []) {
		parent::__construct($appNamespace, $overrides);
		$this->_manglers = $manglers;
	}

	public function setPlugin($plugin) {
		$this->_plugin = $plugin;
	}

	public function getPolicy($resource) {
		$name = is_object($resource) ? get_class($resource) : (is_string($resource) ? $resource : gettype($resource));
		$policy = $this->getTypePolicy($name, $resource, $this->_plugin);
		if ($policy) {
			return $policy;
		}
		throw new MissingPolicyException([$name]);
	}

	protected function getTypePolicy($class, $resource, $plugin) {
		$name = null;
		foreach ($this->_manglers as $type => $callable) {
			$needle = "\\{$type}\\";
			if (strpos($class, $needle) !== false) {
				$namespace = str_replace('\\', '/', substr($class, 0, strpos($class, $needle)));
				$name = substr($class, strpos($class, $needle) + strlen($needle));
				if ($callable) {
					$name = $callable($name, $resource, $this);
				}
			}
		}
		if (!$name) {
			return false;
		}

		if (is_object($name)) {
			// Assume that the callable returned a final policy instead of the name of the policy.
			return $name;
		}

		if ($plugin) {
			$namespace = $plugin;
		}

		return $this->findPolicy($class, $name, $namespace);
	}

}

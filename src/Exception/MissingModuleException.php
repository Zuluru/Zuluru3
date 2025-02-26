<?php
namespace App\Exception;

use Cake\Core\Exception\CakeException;

/**
 * Used when a module cannot be found.
 *
 */
class MissingModuleException extends CakeException {

	protected $_messageTemplate = 'Module class %s could not be found.';
}

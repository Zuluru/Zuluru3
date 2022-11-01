<?php
namespace App\Exception;

use Cake\Core\Exception\Exception;

/**
 * Used when a module cannot be found.
 *
 */
class MissingModuleException extends Exception {

	protected $_messageTemplate = 'Module class %s could not be found.';
}

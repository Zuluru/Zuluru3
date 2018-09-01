<%
/**
 * Controller bake template file
 *
 * Allows templating of Controllers generated from bake.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         0.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
use Cake\Utility\Inflector;

$defaultModel = $name;
%>
<?php
namespace <%= $namespace %>\Controller<%= $prefix %>;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Network\Exception\MethodNotAllowedException;
use Cake\ORM\Entity;
use Cake\ORM\Query;

/**
 * <%= $name %> Controller
 *
 * @property \<%= $namespace %>\Model\Table\<%= $defaultModel %>Table $<%= $defaultModel %>
<%
foreach ($components as $component):
	$classInfo = $this->Bake->classInfo($component, 'Controller/Component', 'Component');
%>
 * @property <%= $classInfo['fqn'] %> $<%= $classInfo['name'] %>
<% endforeach; %>
 */
class <%= $name %>Controller extends AppController {

	/**
	 * _publicActions method
	 *
	 * @return array of actions that can be taken even by visitors that are not logged in.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if <%= $pluralHumanName %> are not enabled
	 */
	protected function _publicActions() {
			if (!Configure::read('feature.<%= $pluralHumanName %>')) {
				throw new MethodNotAllowedException('<%= $defaultModel %> are not enabled on this system.');
			}

			return ['index', 'view'];
	}

	/**
	 * _freeActions method
	 *
	 * @return array list of actions that people can perform even if the system wants them to do something else
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if <%= $pluralHumanName %> are not enabled
	 */
	protected function _freeActions() {
			if (!Configure::read('feature.<%= $singularName %>')) {
				throw new MethodNotAllowedException('<%= $defaultModel %> are not enabled on this system.');
			}

			return ['index'];
	}

	/**
	 * isAuthorized method
	 *
	 * @return bool true if access allowed
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if <%= $pluralHumanName %> are not enabled
	 */
	public function isAuthorized() {
		if ($this->UserCache->read('Person.status') == 'locked') {
			return false;
		}

		if (!Configure::read('feature.<%= $singularName %>')) {
			throw new MethodNotAllowedException('<%= $defaultModel %> are not enabled on this system.');
		}

		// Anyone that's logged in can perform these operations
		if (in_array($this->request->params['action'], [
			'index',
			'view',
		]))
		{
			return true;
		}

		if (Configure::read('Perm.is_manager')) {
			// Managers can perform these operations
			if (in_array($this->request->params['action'], [
				'add',
			]))
			{
				return true;
			}

			// Managers can perform these operations in affiliates they manage
			if (in_array($this->request->params['action'], [
				'edit',
				'delete',
			]))
			{
				// If a <%= $singularName %> id is specified, check if we're a manager of that <%= $singularName %>'s affiliate
				$<%= $singularName %> = $this->request->query('<%= $singularName %>');
				if ($<%= $singularName %>) {
					if (in_array($this-><%= $currentModelName %>->affiliate($<%= $singularName %>), $this->UserCache->read('ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}
		}

		return false;
	}

<%
echo $this->Bake->arrayProperty('helpers', $helpers, ['indent' => false]);
echo $this->Bake->arrayProperty('components', $components, ['indent' => false]);
foreach($actions as $action) {
	echo $this->element('Controller/' . $action);
}
%>

}

<%
/**
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
$allAssociations = array_merge(
	$this->Bake->aliasExtractor($modelObj, 'BelongsTo'),
	$this->Bake->aliasExtractor($modelObj, 'BelongsToMany'),
	$this->Bake->aliasExtractor($modelObj, 'HasOne'),
	$this->Bake->aliasExtractor($modelObj, 'HasMany')
);
%>

	/**
	 * View method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function view() {
		$id = $this->getRequest()->getQuery('<%= $singularName %>');
		try {
			$<%= $singularName%> = $this-><%= $currentModelName %>->get($id, [
				'contain' => [<%= $this->Bake->stringifyList($allAssociations, ['indent' => false]) %>]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid <%= strtolower($singularHumanName) %>.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid <%= strtolower($singularHumanName) %>.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($<%= $singularName%>);

		$this->set(compact('<%= $singularName %>'));
	}

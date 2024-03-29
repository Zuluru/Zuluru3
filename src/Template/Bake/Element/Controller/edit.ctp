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

$belongsTo = $this->Bake->aliasExtractor($modelObj, 'BelongsTo');
$belongsToMany = $this->Bake->aliasExtractor($modelObj, 'BelongsToMany');
$compact = ["'" . $singularName . "'"];
%>

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->getRequest()->getQuery('<%= $singularName %>');
		try {
			$<%= $singularName %> = $this-><%= $currentModelName %>->get($id, [
				'contain' => [<%= $this->Bake->stringifyList($belongsToMany, ['indent' => false]) %>]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid <%= strtolower($singularHumanName) %>.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid <%= strtolower($singularHumanName) %>.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($<%= $singularName%>);

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$<%= $singularName %> = $this-><%= $currentModelName %>->patchEntity($<%= $singularName %>, $this->getRequest()->getData());
			if ($this-><%= $currentModelName; %>->save($<%= $singularName %>)) {
				$this->Flash->success(__('The <%= strtolower($singularHumanName) %> has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The <%= strtolower($singularHumanName) %> could not be saved. Please correct the errors below and try again.'));
			}
		}
<%
		foreach (array_merge($belongsTo, $belongsToMany) as $assoc):
			$association = $modelObj->getAssociation($assoc);
			$otherName = $association->getTarget()->getAlias();
			$otherPlural = $this->_variableName($otherName);
%>
		$<%= $otherPlural %> = $this-><%= $currentModelName %>-><%= $otherName %>->find('list', ['limit' => 200]);
<%
			$compact[] = "'$otherPlural'";
		endforeach;
%>
		$this->set(compact(<%= join(', ', $compact) %>));
	}

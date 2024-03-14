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
use Cake\Utility\Inflector;
%>
<?php
namespace <%= $namespace %>\Model\Table;

<%
$uses = [
	"use $namespace\\Model\\Entity\\$entity;",
	'use Cake\ORM\Query;',
	'use Cake\ORM\RulesChecker;',
	'use Cake\ORM\Table;',
	'use Cake\Validation\Validator;'
];
sort($uses);
echo implode("\n", $uses);
%>


/**
 * <%= $name %> Model
<% if ($associations): %>
 *
<% foreach ($associations as $type => $assocs): %>
<% foreach ($assocs as $assoc): %>
 * @property \Cake\ORM\Association\<%= Inflector::camelize($type) %> $<%= $assoc['alias'] %>
<% endforeach %>
<% endforeach; %>
<% endif; %>
 */
class <%= $name %>Table extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
        parent::initialize($config);

<% if (!empty($table)): %>
		$this->setTable('<%= $table %>');
<% endif %>
<% if (!empty($displayField)): %>
		$this->setDisplayField('<%= $displayField %>');
<% endif %>
<% if (!empty($primaryKey)): %>
<% if (count($primaryKey) > 1): %>
		$this->setPrimaryKey([<%= $this->Bake->stringifyList((array)$primaryKey, ['indent' => false]) %>]);
<% else: %>
		$this->setPrimaryKey('<%= current((array)$primaryKey) %>');
<% endif %>
<% endif %>
<% if (!empty($behaviors)): %>

<% endif; %>
<% foreach ($behaviors as $behavior => $behaviorData): %>
		$this->addBehavior('<%= $behavior %>'<%= $behaviorData ? ", [" . implode(', ', $behaviorData) . ']' : '' %>);
<% endforeach %>
<% if (!empty($associations['belongsTo']) || !empty($associations['hasMany']) || !empty($associations['belongsToMany'])): %>

<% endif; %>
<% foreach ($associations as $type => $assocs): %>
<% if (!empty($assocs)): %>

<% foreach ($assocs as $assoc):
	$alias = $assoc['alias'];
	unset($assoc['alias']);
%>
		$this-><%= $type %>('<%= $alias %>', [<%= $this->Bake->stringifyList($assoc, ['indent' => 3]) %>]);
<% endforeach %>
<% endif %>
<% endforeach %>
	}
<% if (!empty($validation)): %>

	/**
	 * Default validation rules.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault(Validator $validator) {
		$validator
<%
foreach ($validation as $field => $rules):
	foreach ($rules as $ruleName => $rule):
		if ($rule['rule'] && !isset($rule['provider'])):
			printf(
				"			->add('%s', '%s', ['rule' => '%s'])\n",
				$field,
				$ruleName,
				$rule['rule']
			);
		elseif ($rule['rule'] && isset($rule['provider'])):
			printf(
				"			->add('%s', '%s', ['rule' => '%s', 'provider' => '%s'])\n",
				$field,
				$ruleName,
				$rule['rule'],
				$rule['provider']
			);
		endif;

		if (isset($rule['allowEmpty'])):
			if (is_string($rule['allowEmpty'])):
				printf(
					"			->allowEmptyString('%s', '%s')\n",
					$field,
					$rule['allowEmpty']
				);
			elseif ($rule['allowEmpty']):
				printf(
					"			->allowEmptyString('%s')\n",
					$field
				);
			else:
				printf(
					"			->requirePresence('%s', 'create')\n",
					$field
				);
				printf(
					"			->notEmptyString('%s')\n",
					$field
				);
			endif;
		endif;
	endforeach;

	echo "\n";
endforeach;
%>


			;

		return $validator;
	}
<% endif %>
<% if (!empty($rulesChecker)): %>

	/**
	 * Returns a rules checker object that will be used for validating
	 * application integrity.
	 *
	 * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
	 * @return \Cake\ORM\RulesChecker
	 */
	public function buildRules(RulesChecker $rules) {
	<%- foreach ($rulesChecker as $field => $rule): %>
		$rules->add($rules-><%= $rule['name'] %>(['<%= $field %>']<%= !empty($rule['extra']) ? ", '$rule[extra]'" : '' %>));
	<%- endforeach; %>
		return $rules;
	}
<% endif; %>
<% if ($connection != 'default'): %>

	/**
	 * Returns the database connection name to use by default.
	 *
	 * @return string
	 */
	public static function defaultConnectionName() {
		return '<%= $connection %>';
	}
<% endif; %>

}

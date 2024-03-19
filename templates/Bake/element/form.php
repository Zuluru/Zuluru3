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

$fields = collection($fields)
	->filter(function ($field) use ($schema) {
		return $schema->getColumnType($field) !== 'binary';
	});
%>
<?php
$this->Breadcrumbs->add(__('<%= $pluralHumanName %>'));
if ($<%= $singularVar %>->isNew()) {
	$this->Breadcrumbs->add(__('Create'));
} else {
	$this->Breadcrumbs->add(h($<%= $singularVar %>->name));
	$this->Breadcrumbs->add(__('Edit'));
}
?>

<div class="<%= $pluralVar %> form">
	<?= $this->Form->create($<%= $singularVar %>, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= $<%= $singularVar %>->isNew() ? __('Create <%= $singularHumanName %>') : __('Edit <%= $singularHumanName %>') ?></legend>
<?php
<%
		foreach ($fields as $field) {
			if (in_array($field, $primaryKey)) {
				continue;
			}
			if (isset($keyFields[$field])) {
				$fieldData = $schema->getColumn($field);
				if (!empty($fieldData['null'])) {
%>
			echo $this->Form->control('<%= $field %>', ['options' => $<%= $keyFields[$field] %>, 'empty' => true]);
<%
				} else {
%>
			echo $this->Form->control('<%= $field %>', ['options' => $<%= $keyFields[$field] %>]);
<%
				}
				continue;
			}
			if (!in_array($field, ['created', 'modified'])) {
				$fieldData = $schema->getColumn($field);
				if (($fieldData['type'] === 'date') && (!empty($fieldData['null']))) {
%>
			echo $this->Form->control('<%= $field %>', ['empty' => true, 'default' => '']);
<%
				} else {
%>
			echo $this->Form->control('<%= $field %>');
<%
				}
			}
		}
		if (!empty($associations['BelongsToMany'])) {
			foreach ($associations['BelongsToMany'] as $assocName => $assocData) {
%>
			echo $this->Form->control('<%= $assocData['property'] %>._ids', ['options' => $<%= $assocData['variable'] %>]);
<%
			}
		}
%>
?>
	</fieldset>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->link(__('List <%= $pluralHumanName %>'), ['action' => 'index']));
if (!$<%= $singularVar %>->isNew()) {
	echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', '<%= $singularVar %>' => $<%= $singularVar %>-><%= $primaryKey[0] %>],
		['alt' => __('Delete'), 'title' => __('Delete <%= $singularHumanName %>')],
		['confirm' => __('Are you sure you want to delete this <%= strtolower($singularHumanName) %>?')]));
	echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('Add'), 'title' => __('Add <%= $singularHumanName %>')]));
}
?>
	</ul>
</div>

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
		return !in_array($schema->getColumnType($field), ['binary', 'text']);
	})
	->take(7);
%>
<?php
/**
 * @var \App\Model\Entity\<%= $singularHumanName %>[] $<%= $pluralVar %>
 */

$this->Breadcrumbs->add(__('<%= $pluralHumanName %>'));
$this->Breadcrumbs->add(__('List'));
?>

<div class="<%= $pluralVar %> index">
	<h2><?= __('<%= $pluralHumanName %>') ?></h2>
	<p><?= $this->Paginator->counter(
		__('Page {{page}} of {{pages}}, showing {{current}} records out of {{count}} total, starting on record {{start}}, ending on {{end}}')
	) ?></p>
	<div class="table-responsive">
	<table class="table table-striped table-hover table-condensed">
		<thead>
			<tr>
<% foreach ($fields as $field): %>
<% if (!in_array($field, $primaryKey)): %>
				<th><?= $this->Paginator->sort('<%= $field %>') ?></th>
<% endif; %>
<% endforeach; %>
				<th class="actions"><?= __('Actions') ?></th>
			</tr>
		</thead>
		<tbody>
<?php
foreach ($<%= $pluralVar %> as $<%= $singularVar %>):
?>
			<tr>
<%		foreach ($fields as $field) {
			if (!in_array($field, $primaryKey)) {
				$isKey = false;
				if (!empty($associations['BelongsTo'])) {
					foreach ($associations['BelongsTo'] as $alias => $details) {
						if ($field === $details['foreignKey']) {
							$isKey = true;
%>
				<td><?= $<%= $singularVar %>->has('<%= $details['property'] %>') ? $this->Html->link($<%= $singularVar %>-><%= $details['property'] %>-><%= $details['displayField'] %>, ['controller' => '<%= $details['controller'] %>', 'action' => 'view', '?' => [$<%= $singularVar %>-><%= $details['property'] %>-><%= $details['primaryKey'][0] %>]]) : '' ?></td>
<%
							break;
						}
					}
				}
				if ($isKey !== true) {
					$type = $schema->getColumnType($field);
					if (in_array($type, ['integer', 'biginteger', 'decimal', 'float'])) {
%>
				<td><?= $this->Number->format($<%= $singularVar %>-><%= $field %>) ?></td>
<%
					} else if ($type == 'date') {
%>
				<td><?= $this->Time->date($<%= $singularVar %>-><%= $field %>) ?></td>
<%
					} else if ($type == 'time') {
%>
				<td><?= $this->Time->time($<%= $singularVar %>-><%= $field %>) ?></td>
<%
					} else if (in_array($type, ['datetime', 'timestamp'])) {
%>
				<td><?= $this->Time->datetime($<%= $singularVar %>-><%= $field %>) ?></td>
<%
					} else if ($type == 'boolean') {
%>
				<td><?= $<%= $singularVar %>-><%= $field %> ? __('Yes') : __('No') ?></td>
<%
					} else {
%>
				<td><?= h($<%= $singularVar %>-><%= $field %>) ?></td>
<%
					}
				}
			}
		}

		$pk = '$' . $singularVar . '->' . $primaryKey[0];
%>
				<td class="actions"><?php
				echo $this->Html->iconLink('view_24.png',
					['action' => 'view', '?' => ['<%= $singularVar %>' => <%= $pk %>]],
					['alt' => __('View'), 'title' => __('View')]);
				if ($this->Authorize->can('edit', $<%= $singularVar %>)) {
					echo $this->Html->iconLink('edit_24.png',
						['action' => 'edit', '?' => ['<%= $singularVar %>' => <%= $pk %>]],
						['alt' => __('Edit'), 'title' => __('Edit')]);
				}
				if ($this->Authorize->can('delete', $<%= $singularVar %>)) {
					echo $this->Form->iconPostLink('delete_24.png',
						['action' => 'delete', '?' => ['<%= $singularVar %>' => <%= $pk %>]],
						['alt' => __('Delete'), 'title' => __('Delete')],
						['confirm' => __('Are you sure you want to delete this <%= strtolower($singularHumanName) %>?')]);
				}
				?></td>
			</tr>

<?php
endforeach;
?>
		</tbody>
	</table>
	</div>
	<nav class="paginator"><ul class="pagination">
		<?= $this->Paginator->numbers(['prev' => true, 'next' => true]) ?>
	</ul></nav>
</div>
<?php
if ($this->Authorize->can('add', \App\Controller\<%= $pluralHumanName %>Controller::class)):
?>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
	['action' => 'add'],
	['alt' => __('Add'), 'title' => __('Add <%= $singularHumanName %>')]));
?>
	</ul>
</div>
<?php
endif;

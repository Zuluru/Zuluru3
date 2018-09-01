<?php
use Cake\Core\Configure;
?>

<h2><?php
echo $field->long_name;
?></h2>
<dl class="dl-horizontal">
	<dt><?= __('Address') ?></dt>
	<dd><?= $field->facility->location_street ?></dd>
	<dt><?= __('City') ?></dt>
	<dd><?= $field->facility->location_city ?></dd>
	<dt><?= __('Region') ?></dt>
	<dd><?= $field->facility->region->name ?></dd>
	<dt><?= __('Surface') ?></dt>
	<dd><?= __(Configure::read("options.surface.{$field->surface}")) ?></dd>
<?php
if ($field->length > 0):
?>
	<dt><?= __('Map') ?></dt>
	<dd><?= $this->Html->link(__('Open in new window'),
		['controller' => 'Maps', 'action' => 'view', 'field' => $field->id],
		['target' => 'map']) ?></dd>
<?php
endif;

if (!empty($field->layout_url)): ?>
	<dt><?= __('Layout') ?></dt>
	<dd><?= $this->Html->link(__('Open in new window'),
		$field->layout_url,
		['target' => 'map']) ?></dd>
<?php
endif;

if (!empty($field->facility->permit_url)): ?>
	<dt><?= __('Permit') ?></dt>
	<dd><?= $this->Html->link($field->facility->permit_name, $field->facility->permit_url) ?></dd>
<?php
endif;
?>
</dl>

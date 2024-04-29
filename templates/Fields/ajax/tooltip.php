<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Field $field
 */

use Cake\Core\Configure;
?>

<h2><?php
echo $field->long_name;
?></h2>
<dl class="row">
	<dt class="col-sm-2 text-end"><?= __('Address') ?></dt>
	<dd class="col-sm-10 mb-0"><?= $field->facility->location_street ?></dd>
	<dt class="col-sm-2 text-end"><?= __('City') ?></dt>
	<dd class="col-sm-10 mb-0"><?= $field->facility->location_city ?></dd>
	<dt class="col-sm-2 text-end"><?= __('Region') ?></dt>
	<dd class="col-sm-10 mb-0"><?= $field->facility->region->name ?></dd>
	<dt class="col-sm-2 text-end"><?= __('Surface') ?></dt>
	<dd class="col-sm-10 mb-0"><?= __(Configure::read("options.surface.{$field->surface}")) ?></dd>
<?php
if ($field->length > 0):
?>
	<dt class="col-sm-2 text-end"><?= __('Map') ?></dt>
	<dd class="col-sm-10 mb-0"><?= $this->Html->link(__('Open in new window'),
		['controller' => 'Maps', 'action' => 'view', '?' => ['field' => $field->id]],
		['target' => 'map']) ?></dd>
<?php
endif;

if (!empty($field->layout_url)): ?>
	<dt class="col-sm-2 text-end"><?= __('Layout') ?></dt>
	<dd class="col-sm-10 mb-0"><?= $this->Html->link(__('Open in new window'),
		$field->layout_url,
		['target' => 'map']) ?></dd>
<?php
endif;

if (!empty($field->facility->permit_url)): ?>
	<dt class="col-sm-2 text-end"><?= __('Permit') ?></dt>
	<dd class="col-sm-10 mb-0"><?= $this->Html->link($field->facility->permit_name, $field->facility->permit_url) ?></dd>
<?php
endif;
?>
</dl>

<?php
$this->Html->addCrumb(__('Settings'));
$this->Html->addCrumb(__('phpBB3'));
?>

<div class="settings form">
<?php
echo $this->Form->create(false, ['align' => 'horizontal']);

echo $this->element('Settings/banner');

echo $this->element('Settings/input', [
	'category' => 'phpbb3',
	'name' => 'root_path',
	'options' => [
		'help' => __('Path to your phpBB3 installation, where config.php is located. Include the trailing slash.'),
	],
]);

echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>

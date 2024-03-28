<?php
/**
 * @var \App\View\AppView $this
 */

$this->Breadcrumbs->add(__('Settings'));
$this->Breadcrumbs->add('phpBB3');
?>

<div class="settings form">
<?php
echo $this->Form->create(null, ['align' => 'horizontal']);

echo $this->element('Settings/banner');

echo $this->element('Settings/input', [
	'category' => 'phpbb3',
	'name' => 'root_path',
	'options' => [
		'label' => 'Installation Path',
		'help' => __('Path to your phpBB3 installation, where config.php is located. Include the trailing slash.'),
	],
]);

echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>

<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Affiliate $affiliate
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Settings'));
$this->Breadcrumbs->add(__('Email'));
?>

<div class="settings form">
<?php
echo $this->Form->create(null, ['align' => 'horizontal']);

echo $this->element('Settings/banner');
?>
	<fieldset>
		<legend><?= __('Sender') ?></legend>
<?php
if (!$affiliate) {
	echo $this->element('Settings/input', [
		'category' => 'email',
		'name' => 'admin_name',
		'options' => [
			'label' => __('Admin Name'),
			'help' => __('The name (or descriptive role) of the system administrator. Mail from {0} will come from this name.', ZULURU),
		],
	]);
	echo $this->element('Settings/input', [
		'category' => 'email',
		'name' => 'admin_email',
		'options' => [
			'label' => __('Admin Email'),
			'help' => __('The e-mail address of the system administrator. Mail from {0} will come from this address.', ZULURU),
		],
	]);
	echo $this->element('Settings/input', [
		'category' => 'email',
		'name' => 'support_email',
		'options' => [
			'label' => __('Support Email'),
			'help' => __('The e-mail address for system support. This address will be linked for bug reports, etc.'),
		],
	]);
}
if (Configure::read('scoring.incident_reports')) {
	echo $this->element('Settings/input', [
		'category' => 'email',
		'name' => 'incident_report_email',
		'options' => [
			'label' => __('Incident Report Email'),
			'help' => __('The e-mail address to send incident reports to, if enabled.'),
		],
	]);
}
?>
	</fieldset>
<?php
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>

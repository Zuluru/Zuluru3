<?php
/**
 * @type \App\Model\Entity\Affiliate $affiliate
 */

use Cake\Core\Configure;

$this->Html->addCrumb(__('Settings'));
$this->Html->addCrumb('Javelin');
?>

<div class="settings form">
<?php
if ($affiliate) {
	$empty = __('Use default');
} else {
	$empty = false;
}
echo $this->Form->create(false, ['align' => 'horizontal']);

echo $this->element('Settings/banner');
?>
<fieldset>
	<legend><?= __('{0} Settings', 'Javelin') ?></legend>
<?php
echo $this->element('Settings/input', [
	'category' => 'javelin',
	'name' => 'default_opt_in',
	'options' => [
		'label' => __('Default Opt-in'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'confirm' => __('By enabling this, you are making {0} an "opt out" service, where players must explicitly opt out if they do not want to participate.\n\nIf this is disabled, then players must explicitly opt in.\n\nBefore enabling, be sure that your local privacy laws allow it.', 'Javelin'),
	],
]);
?>
</fieldset>
<?php
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>

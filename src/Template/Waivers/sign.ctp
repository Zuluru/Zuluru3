<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Waiver $waiver
 */

use Cake\Core\Configure;

$this->Html->addCrumb(__('Players'));
$this->Html->addCrumb($person->full_name);
$this->Html->addCrumb(__('Sign Waiver'));
$this->Html->addCrumb($waiver->name);
?>

<?php
$variables = [
	'%name%' => Configure::read('organization.name'),
	'%short_name%' => Configure::read('organization.short_name'),
	'%field%' => Configure::read('UI.field'),
	'%fields%' => Configure::read('UI.fields'),
	'%Field%' => Configure::read('UI.field_cap'),
	'%Fields%' => Configure::read('UI.fields_cap'),
	'%valid_from%' => $valid_from->i18nFormat('MMMM d, yyyy'),
	'%valid_from_year%' => $valid_from->year,
	'%valid_until%' => $valid_until->i18nFormat('MMMM d, yyyy'),
	'%valid_until_year%' => $valid_until->year,
];
if ($variables['%valid_from_year%'] == $variables['%valid_until_year%']) {
	$variables['%valid_years%'] = $variables['%valid_from_year%'];
} else {
	$variables['%valid_years%'] = "{$variables['%valid_from_year%']}-{$variables['%valid_until_year%']}";
}
echo strtr($waiver->text, $variables);

echo $this->Form->create($waiver, ['align' => 'horizontal']);
echo $this->Html->para(null,
	$this->Form->input('signed', [
		'options' => [
			'yes' => __('I agree to the above conditions'),
			'no' => __('I DO NOT agree to the above conditions'),
		],
		'type' => 'radio',
		'label' => false,
	])
);
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();

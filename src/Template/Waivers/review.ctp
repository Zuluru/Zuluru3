<?php
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;

$this->Html->addCrumb(__('Players'));
$this->Html->addCrumb($person->full_name);
$this->Html->addCrumb(__('View Waiver'));
$this->Html->addCrumb($waiver->name);
?>

<?php
if (!empty($person->waivers)) {
	if (isset($date)) {
		$message = __('You accepted this waiver at %s on %s');
	} else {
		$message = __('You most recently accepted this waiver at %s on %s');
	}
	if ($person->waivers[0]->expiry_type != 'never') {
		$message .= ', ' . __('covering the dates %s to %s');

	}
	$message .= '.';

	$message = sprintf($message,
		$this->Time->time($person->waivers[0]->_joinData->created), $this->Time->fulldate($person->waivers[0]->_joinData->created),
		$this->Time->fulldate($person->waivers[0]->_joinData->valid_from), $this->Time->fulldate($person->waivers[0]->_joinData->valid_until));
} else {
	$url = ['action' => 'sign', 'waiver' => $waiver->id];
	if (isset($date)) {
		$message = __('You have not accepted this waiver for the dates {0} to {1}.',
				$this->Time->fulldate($valid_from), $this->Time->fulldate($valid_until));
		$url['date'] = $date->toDateString();
	} else {
		$message = __('You haven\'t accepted this waiver.');
		$url['date'] = FrozenDate::now()->toDateString();
	}
	if ($waiver->active) {
		$message .= ' ' . __('You may {0}; if you choose not to, you may be prompted to do so at a later time.',
			$this->Html->link(__('accept it now'), $url));
	}
}
echo $this->Html->para('highlight-message', $message);

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
echo strtr($waiver['text'], $variables);

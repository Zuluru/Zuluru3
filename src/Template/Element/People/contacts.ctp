<?php
use App\Controller\AppController;
use Cake\Core\Configure;

$lines = [];

if (!empty($person->email) &&
	($view_contact || (Configure::read('Perm.is_logged_in') && $person->publish_email))
) {
	$lines[] = $this->Html->link($person->email, "mailto:{$person->email}");
}
if (!empty($person->alternate_email) &&
	($view_contact || (Configure::read('Perm.is_logged_in') && $person->publish_alternate_email))
) {
	$lines[] = $this->Html->link($person->alternate_email, "mailto:{$person->alternate_email}");
}
if (!empty($person->home_phone) &&
	($view_contact || (Configure::read('Perm.is_logged_in') && $person->publish_home_phone))
) {
	$lines[] = $person->home_phone . ' (' . __('home') . ')';
}
if (!empty($person->work_phone) &&
	($view_contact || (Configure::read('Perm.is_logged_in') && $person->publish_work_phone))
) {
	$line = $person->work_phone;
	if (!empty($person->work_ext)) {
		$line .= ' x' . $person->work_ext;
	}
	$line .= ' (' . __('work') . ')';
	$lines[] = $line;
}
if (!empty($person->mobile_phone) &&
	($view_contact || (Configure::read('Perm.is_logged_in') && $person->publish_mobile_phone))
) {
	$lines[] = $person->mobile_phone . ' (' . __('mobile') . ')';
}

echo implode($this->Html->tag('br'), $lines);

if (Configure::read('Perm.is_logged_in')) {
	$links = [];
	if (!empty($lines)) {
		$links[] = $this->Html->link(__('VCF'), ['action' => 'vcf', 'person' => $person->id]);
	}
	if (Configure::read('feature.annotations')) {
		$links[] = $this->Html->link(__('Add Note'), ['action' => 'note', 'person' => $person->id]);
	}
	if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || array_key_exists($person->id, $this->UserCache->allActAs())) {
		$links[] = $this->Html->link(__('Act As'), ['action' => 'act_as', 'person' => $person->id]);
	}
	if (!empty($links)) {
		echo $this->Html->tag('br') . implode(' / ', $links);
	}
}

<?php
use Cake\Core\Configure;
?>

<p><?= __('The "edit {0}" page is used to update details of your {1}.',
	__(Configure::read('UI.field')), __(Configure::read('UI.fields'))
);
?></p>
<p><?= __('The "create {0}" page is essentially identical to this page.',
	__(Configure::read('UI.field'))
);
?></p>
<?php
echo $this->element('Help/topics', [
	'section' => 'fields/edit',
	'topics' => [
		'num' => 'Number',
		'is_open',
	],
]);

<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;

$this->Html->addCrumb(__('Users'));
$this->Html->addCrumb(__('Import'));
?>

<div class="users view">
<h2><?= __('Import Users') ?></h2>

<?php
if (isset($header)):
?>
<p><?= __('The following columns were recognized and will be imported:') . ' ' . implode(', ', $header) ?></p>
<?php
	if (!empty($skip)):
?>
<p><?= __('The following columns were not recognized and will be skipped:') . ' ' . implode(', ', $skip) ?></p>
<?php
	endif;
endif;

if (!empty($succeeded)):
?>
<p><?= __('{0} accounts had no problems', count($succeeded)) ?>: <a class="success_hidden" href="#"><?= __('Show details') ?></a><a class="success_details" href="#"><?= __('Hide details') ?></a></p>
<div class="success_details"><?= $this->Html->nestedList($succeeded) ?></div>
<?php
$this->Html->scriptBlock('zjQuery("a.success_hidden").bind("click", function (event) { zjQuery(".success_details").show(); zjQuery(".success_hidden").hide(); return false; });', ['buffer' => true]);
$this->Html->scriptBlock('zjQuery("a.success_details").bind("click", function (event) { zjQuery(".success_details").hide(); zjQuery(".success_hidden").show(); return false; });', ['buffer' => true]);
$this->Html->scriptBlock('zjQuery(".success_details").hide();', ['buffer' => true]);
endif;

if (!empty($resolved)):
?>
<p><?= __('{0} accounts had one or more problems which were resolved', count($resolved)) ?>: <a class="resolved_hidden" href="#"><?= __('Show details') ?></a><a class="resolved_details" href="#"><?= __('Hide details') ?></a></p>
<div class="resolved_details"><?= $this->Html->nestedList($resolved) ?></div>
<?php
	$this->Html->scriptBlock('zjQuery("a.resolved_hidden").bind("click", function (event) { zjQuery(".resolved_details").show(); zjQuery(".resolved_hidden").hide(); return false; });', ['buffer' => true]);
	$this->Html->scriptBlock('zjQuery("a.resolved_details").bind("click", function (event) { zjQuery(".resolved_details").hide(); zjQuery(".resolved_hidden").show(); return false; });', ['buffer' => true]);
	$this->Html->scriptBlock('zjQuery(".resolved_details").hide();', ['buffer' => true]);
endif;
?>

<?php
if (!empty($failed)):
?>
<p><?= __('{0} accounts had one more more unresolvable problems', count($failed)) ?>: <a class="failure_hidden" href="#"><?= __('Show details') ?></a><a class="failure_details" href="#"><?= __('Hide details') ?></a></p>
<div class="failure_details"><?= $this->Html->nestedList($failed) ?></div>
<?php
	$this->Html->scriptBlock('zjQuery("a.failure_hidden").bind("click", function (event) { zjQuery(".failure_details").show(); zjQuery(".failure_hidden").hide(); return false; });', ['buffer' => true]);
	$this->Html->scriptBlock('zjQuery("a.failure_details").bind("click", function (event) { zjQuery(".failure_details").hide(); zjQuery(".failure_hidden").show(); return false; });', ['buffer' => true]);
	$this->Html->scriptBlock('zjQuery(".failure_details").hide();', ['buffer' => true]);
endif;
?>

<?php
echo $this->Form->create($user, ['align' => 'horizontal', 'type' => 'file']);
echo $this->Form->input('file', ['type' => 'file', 'label' => __('CSV file')]);
if (Configure::read('feature.multiple_affiliates')) {
	echo $this->Form->input('person.affiliates._ids', [
		'help' => __('Select all affiliates you are interested in.'),
		'multiple' => 'checkbox',
		'hiddenField' => false,
	]);
} else {
	echo $this->Form->input('person.affiliates.0.id', [
		'label' => __('Affiliate'),
		'options' => $affiliates,
		'type' => 'select',
		'empty' => '---',
	]);
}

echo $this->Form->input('person.trim_email_domain', [
	'type' => 'checkbox',
	'help' => __('If checked, and a user name is created from an email address, the domain portion of the email address will be removed first. If duplicates are caused this way, they will be numbered 2, 3, etc.'),
]);
echo $this->Form->input('person.trial_run', [
	'type' => 'checkbox',
	'help' => __('If checked, no users will be created; the file will be tested and a report generated.'),
]);
echo $this->Form->input('person.status', [
	'label' => __('Status to set for imported accounts'),
	'options' => Configure::read('options.record_status'),
	'empty' => '---',
]);
echo $this->Form->input('person.groups._ids', [
	'label' => __('Select groups for new users to be added to.'),
	'type' => 'select',
	'multiple' => 'checkbox',
	'hiddenField' => false,
	'options' => $groups,
	'hide_single' => true,
]);
echo $this->Form->input('person.on_error', [
	'options' => [
		'skip' => 'Skip record',
		'blank' => 'Import blank field',
		'ignore' => 'Ignore errors and import data as-is',
	],
	'empty' => '---',
	'help' => __('Note that the email and user name fields cannot be blank, so records with errors in those fields will always be skipped. This has no effect if "trial run" is checked above.'),
]);
echo $this->Form->input('person.notify_new_users', [
	'type' => 'checkbox',
	'help' => __('If checked, new users will receive an email with their user name and password. This has no effect if "trial run" is checked above.'),
]);
echo $this->Form->button(__('Upload'), ['class' => 'btn-success']);
echo $this->Form->end();
?>

<ul>
<li><?= __('File to be imported must have column names in the first row.') ?></li>
<li><?= __('The only required column is email.') ?></li>
<li><?= __('If there is no user_name column, or if the user_name column is blank for a user, their email address will be used as their user name.') ?></li>
<li><?= __('If there is no password column, or if the password column is blank for a user, a random one will be generated.') ?></li>
<li><?= __('An id column may be included, but this is discouraged unless you really know what you\'re doing.') ?></li>
<li><?= __('Other optional columns are') . ' ' . implode(', ', $columns) ?>.
<li><?= __('Any other columns will be ignored.') ?></li>
<li><?= __('Column names must match these names exactly, including case-sensitivity and underscores where present.') ?></li>
<?php
if (in_array('birthdate', $columns)):
?>
<li><?php
	if (Configure::read('feature.birth_year_only')):
		echo __('Birthdate may be specified in YYYY-MM-DD or YYYY format.');
	else:
		echo __('Birthdate must be specified in YYYY-MM-DD format.');
	endif;
?></li>
<?php
endif;
?>
<li><?= __('Rows starting with a # will be skipped.') ?></li>
<li><?= __('Rows where the email address is set to simply "child" (without the quotes) will be created as a child profile automatically linked to the previous account. Multiple children can be added this way.') ?></li>
</ul>
</div>

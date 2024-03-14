<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Badge $badge
 */

$this->Html->addCrumb(__('Badges'));
if ($badge->category == 'assigned') {
	$this->Html->addCrumb(__('Assign'));
} else {
	$this->Html->addCrumb(__('Nominate'));
}
if (count($affiliates) > 1) {
	$this->Html->addCrumb($badge->affiliate->name);
}
$this->Html->addCrumb($badge->name);
$this->Html->addCrumb($person->full_name);
?>

<div class="badges form">
<?= $this->Form->create() ?>
	<fieldset>
		<legend><?php
			if (count($affiliates) > 1) {
				echo "{$badge->affiliate->name} ";
			}
			if ($badge->category == 'assigned') {
				echo __('Assign "{0}" Badge to {1}', $badge->name, $person->full_name);
			} else {
				echo __('Nominate {0} for the "{1}" Badge', $person->full_name, $badge->name);
			}
		?></legend>
		<p><?= $this->Html->iconImg($badge->icon . '_64.png') . ' ' . $badge->description ?></p>
<?php
if ($badge->category == 'nominated'):
?>
	<p><?= __('Most badges are a sign of prestige, and are not simply granted to everyone. Here you can provide a reason why this person deserves this badge, which will be provided to the administrator to aid their decision. If approved, this reason will also be visible to anyone logged into the system as part of the nominee\'s permanent record.') ?></p>
<?php
elseif ($badge->visibility == BADGE_VISIBILITY_ADMIN):
?>
		<p><?= __('This badge is only visible to admins. If you add a reason here, it will be visible to other admins to explain why the badge was assigned. This is typically used in the case of "red flagging" or similar situations.') ?></p>
<?php
endif;

echo $this->Form->control('reason', [
	'cols' => 70,
]);
?>
	</fieldset>
<?php
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>

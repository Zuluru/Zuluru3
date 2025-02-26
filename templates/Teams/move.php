<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\Division[] $divisions
 * @var bool $loose
 */

$this->Breadcrumbs->add(__('Team'));
$this->Breadcrumbs->add($team->name);
$this->Breadcrumbs->add(__('Move'));
?>

<div class="teams move">
<h2><?= __('Move Team') . ': ' . $team->name ?></h2>

<?php
if ($loose):
?>
<p><?= __('The list below includes all open divisions of the same sport. You can return to the {0} if desired.',
    $this->Html->link(__('list of divisions in the same league'), ['?' => ['team' => $team->id]])
) ?></p>
<?php
else:
?>
<p><?= __('By default, {0} allows teams to be moved from one division to another within the same league, but you can {1} if required.',
    ZULURU,
    $this->Html->link(__('remove this restriction'), ['?' => ['team' => $team->id, 'loose' => true]])
) ?></p>
<?php
endif;

echo $this->Form->create($team, ['align' => 'horizontal']);
echo $this->Form->control('to', [
	'label' => __('Division to move this team to:'),
	'options' => collection($divisions)->combine('id', 'full_league_name')->toArray(),
]);

// TODO: Option for swapping this team with another, dynamically load team list into
// drop-down when "swap" checkbox is checked and a destination is selected

echo $this->Form->button(__('Move'), ['class' => 'btn-success']);
echo $this->Form->end();
?>

</div>

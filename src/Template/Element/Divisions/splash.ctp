<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division[] $divisions
 * @var string $comment
 */

if (!empty($divisions)):
?>
<div class="table-responsive">
	<table class="table table-striped table-hover table-condensed">
		<thead>
			<tr>
				<th colspan="2"><?= __('Divisions Coordinated') ?></th>
			</tr>
		</thead>
		<tbody>
<?php
	$coordinated_divisions = $this->UserCache->read('DivisionIDs');
	foreach ($divisions as $division):
?>
			<tr>
				<td class="splash_item"><?= $this->element('Divisions/block', ['division' => $division, 'field' => 'long_league_name']) ?></td>
				<td class="actions splash-action"><?= $this->element('Divisions/actions', ['league' => $division['league'], 'division' => $division]) ?></td>
			</tr>

<?php
	endforeach;
?>
		</tbody>
	</table>
</div>
<?php
endif;

<?php
/**
 * @type $leagues \App\Model\Entity\League[]
 * @type $sport string
 * @type $tournaments bool
 * @type $affiliates int[]
 * @type $affiliate int
 * @type $years int[]
 */

use App\Controller\AppController;
use Cake\Utility\Inflector;

$this->Html->addCrumb($tournaments ? __('Tournaments') : __('Leagues'));
$this->Html->addCrumb(__('List'));
if (!empty($sport)) {
	$this->Html->addCrumb(__(Inflector::humanize($sport)));
}
?>

<div class="leagues index">
	<h2><?= $tournaments ? __('Tournaments') : __('Leagues') ?></h2>
<?php
if (empty($leagues)):
	echo $this->Html->para('warning-message', __('There are no leagues currently active. Please check back periodically for updates{0}.',
		!empty($years) ? ' ' . __('or use the links below to review historical information') : ''));
else:
	$sports = array_unique(collection($leagues)->extract('sport')->toArray());
	echo $this->element('selector', [
		'title' => 'Sport',
		'options' => $sports,
	]);

	$seasons = array_unique(collection($leagues)->extract('long_season')->toArray());
	echo $this->element('selector', [
		'title' => 'Season',
		'options' => $seasons,
	]);

	$days = collection($leagues)->extract('divisions.{*}.days.{*}')->combine('id', 'name')->toArray();
	ksort($days);
	echo $this->element('selector', [
		'title' => 'Day',
		'options' => $days,
	]);
?>
	<div class="table-responsive clear-float">
	<table class="table table-hover table-condensed">
<?php
	$affiliate_id = null;

	foreach ($leagues as $league):
		$this->start('thead');

		if ($league->affiliate_id != $affiliate_id):
			$affiliate_id = $league->affiliate_id;
			$affiliate_leagues = collection($leagues)->filter(function ($league) use ($affiliate_id) {
				return $league->affiliate_id == $affiliate_id;
			});
			$current_sport = null;

			if (count($affiliates) > 1):
				$affiliate_sports = array_unique($affiliate_leagues->extract('sport')->toArray());
				$affiliate_seasons = array_unique($affiliate_leagues->extract('long_season')->toArray());
				$affiliate_days = array_unique($affiliate_leagues->extract('divisions.{*}.days.{*}.name')->toArray());
?>
			<tr class="<?= $this->element('selector_classes', ['title' => 'Sport', 'options' => $affiliate_sports]) ?> <?= $this->element('selector_classes', ['title' => 'Season', 'options' => $affiliate_seasons]) ?> <?= $this->element('selector_classes', ['title' => 'Day', 'options' => $affiliate_days]) ?>">
				<th<?= $this->Authorize->can('edit', $league->affiliate) ? '' : ' colspan="2"' ?>>
					<h3 class="affiliate"><?= h($league->affiliate->name) ?></h3>
				</th>
<?php
				if ($this->Authorize->can('edit', $league->affiliate)):
?>
				<th class="actions"><?php
					echo $this->Html->iconLink('edit_24.png',
						['controller' => 'Affiliates', 'action' => 'edit', 'affiliate' => $league->affiliate_id, 'return' => AppController::_return()],
						['alt' => __('Edit'), 'title' => __('Edit Affiliate')]);
				?></th>
<?php
				endif;
?>
			</tr>
<?php
			endif;
		endif;

		if ($league->sport != $current_sport):
			$current_sport = $league->sport;
			$sport_leagues = $affiliate_leagues->filter(function ($league) use ($current_sport) {
				return $league->sport == $current_sport;
			});
			$season = null;

			if (count($sports) > 1):
				$sport_seasons = array_unique($sport_leagues->extract('long_season')->toArray());
				$sport_days = array_unique($sport_leagues->extract('divisions.{*}.days.{*}.name')->toArray());
?>
			<tr class="<?= $this->element('selector_classes', ['title' => 'Sport', 'options' => $current_sport]) ?> <?= $this->element('selector_classes', ['title' => 'Season', 'options' => $sport_seasons]) ?> <?= $this->element('selector_classes', ['title' => 'Day', 'options' => $sport_days]) ?>">
				<th colspan="2"><?= Inflector::humanize($current_sport) ?></th>
			</tr>
<?php
			endif;
		endif;

		if ($league->long_season != $season):
			$season = $league->long_season;
			if (count($seasons) > 1):
				$season_days = array_unique($sport_leagues->filter(function ($league) use ($season) {
					return $league->long_season == $season;
				})->extract('divisions.{*}.days.{*}.name')->toArray());
?>
			<tr class="<?= $this->element('selector_classes', ['title' => 'Sport', 'options' => $current_sport]) ?> <?= $this->element('selector_classes', ['title' => 'Season', 'options' => $season]) ?> <?= $this->element('selector_classes', ['title' => 'Day', 'options' => $season_days]) ?>">
				<th colspan="2"><?= $season ?></th>
			</tr>
<?php
			endif;
		endif;

		$this->end('thead');
		$thead = $this->fetch('thead');
		if (!empty($thead)) {
			echo $this->Html->tag('thead', $thead);
		}
?>
		<tbody>
<?php
		// If the league has only a single division, we'll merge the details
		$collapse = (count($league->divisions) == 1);
		if ($collapse):
			$class = 'inner-border';
		else:
			$class = '';
			$division_days = collection($league->divisions)->extract('days.{*}.name')->toList();
?>
			<tr class="<?= $this->element('selector_classes', ['title' => 'Sport', 'options' => $current_sport]) ?> <?= $this->element('selector_classes', ['title' => 'Season', 'options' => $season]) ?> <?= $this->element('selector_classes', ['title' => 'Day', 'options' => $division_days]) ?>">
				<td<?= $this->Authorize->can('edit', $league) ? '' : ' colspan="2"' ?> class="inner-border">
					<strong><?= $this->element('Leagues/block', ['league' => $league, 'field' => 'name',  'tournaments' => $tournaments]) ?></strong>
				</td>
<?php
			if ($this->Authorize->can('edit', $league)):
?>
				<td class="actions inner-border"><?= $this->element('Leagues/actions', compact('league', 'tournaments')) ?></td>
<?php
			endif;
?>
			</tr>
<?php
		endif;

		foreach ($league->divisions as $division):
			$division_days = collection($division->days)->extract('name')->toArray();
?>
			<tr class="<?= $this->element('selector_classes', ['title' => 'Sport', 'options' => $current_sport]) ?> <?= $this->element('selector_classes', ['title' => 'Season', 'options' => $season]) ?> <?= $this->element('selector_classes', ['title' => 'Day', 'options' => $division_days]) ?>">
				<td class="<?= $class ?>"><?php
					if ($collapse) {
						$name = $league->name;
						if (!empty($division->name)) {
							$name .= " {$division->name}";
						}
						echo $this->Html->tag('strong',
							$this->element('Leagues/block', ['league' => $league, 'name' => $name,  'tournaments' => $tournaments]));
					} else {
						echo '&nbsp;&nbsp;&nbsp;&nbsp;' .
							$this->element('Divisions/block', ['division' => $division,  'tournaments' => $tournaments]);
					}
				?></td>
				<td class="actions<?= " $class" ?>">
					<?= $this->element('Divisions/actions', compact('division', 'league', 'collapse', 'tournaments')) ?>
				</td>
			</tr>

<?php
		endforeach;
?>
		</tbody>
<?php
	endforeach;
?>
	</table>
	</div>
<?php
endif;
?>
</div>
<?php
if (!empty($years)):
?>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
foreach ($years as $year) {
	echo $this->Html->tag('li', $this->Html->link($year['year'], ['affiliate' => $affiliate, 'sport' => $sport, 'year' => $year['year']]));
}
?>
	</ul>
</div>
<?php
endif;

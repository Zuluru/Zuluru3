<?php
/**
 * @var \App\Model\Entity\League[] $leagues
 * @var string $sport
 * @var bool $tournaments
 * @var int[] $affiliates
 * @var int $affiliate
 * @var int[] $years
 */

use App\Controller\AppController;
use App\Model\Entity\League;
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
	$sports = $this->Selector->extractOptions($leagues, null, 'sport');
	echo $this->Selector->selector('Sport', $sports);

	$seasons = $this->Selector->extractOptionsUnsorted($leagues, null, 'long_season');
	echo $this->Selector->selector('Season', $seasons);

	echo $this->Selector->selector('Day', $this->Selector->extractOptions(
		$leagues,
		function (League $item) { return collection($item->divisions)->extract('days.{*}')->toArray(); },
		'name', 'id'
	));
?>
	<div class="table-responsive clear-float">
	<table class="table table-hover table-condensed">
<?php
	$affiliate_id = $current_sport = $season = null;

	foreach ($leagues as $league):
		$this->start('thead');

		if ($league->affiliate_id !== $affiliate_id):
			$affiliate_id = $league->affiliate_id;
			$affiliate_leagues = collection($leagues)->filter(function ($league) use ($affiliate_id) {
				return $league->affiliate_id === $affiliate_id;
			});
			$current_sport = null;

			if (count($affiliates) > 1):
				$classes = $affiliate_leagues->extract(function (League $league) { return "select_id_{$league->id}"; })->toArray();
?>
			<tr class="<?= implode(' ', $classes) ?>">
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

		if ($league->sport !== $current_sport):
			$current_sport = $league->sport;
			$classes = $affiliate_leagues->filter(function (League $league) use ($current_sport) {
				return $league->sport === $current_sport;
			})->extract(function (League $league) {
				return "select_id_{$league->id}";
			})->toArray();
			$season = null;

			if (count($sports) > 1):
?>
			<tr class="<?= implode(' ', $classes) ?>">
				<th colspan="2"><?= Inflector::humanize($current_sport) ?></th>
			</tr>
<?php
			endif;
		endif;

		if ($league->long_season !== $season):
			$season = $league->long_season;
			if (count($seasons) > 1):
				$classes = $affiliate_leagues->filter(function (League $league) use ($current_sport, $season) {
					return $league->sport === $current_sport && $league->long_season === $season;
				})->extract(function (League $league) {
					return "select_id_{$league->id}";
				})->toArray();
?>
			<tr class="<?= implode(' ', $classes) ?>">
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
		$collapse = (count($league->divisions) === 1);
		if ($collapse):
			$class = 'inner-border';
		else:
			$class = '';
?>
			<tr class="select_id_<?= $league->id ?>">
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
?>
			<tr class="select_id_<?= $league->id ?>">
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
	echo $this->Html->tag('li', $this->Html->link($year, ['affiliate' => $affiliate, 'sport' => $sport, 'year' => $year]));
}
?>
	</ul>
</div>
<?php
endif;

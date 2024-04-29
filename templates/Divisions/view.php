<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 */

use Cake\Core\Configure;
use Cake\Utility\Inflector;

$this->Breadcrumbs->add(__('Divisions'));
$this->Breadcrumbs->add(h($division->full_league_name));
$this->Breadcrumbs->add(__('View'));
?>

<?php
if (!empty($division->header)):
?>
<div class="division_header"><?= $division->header ?></div>
<?php
endif;
?>
<div class="divisions view">
	<h2><?= h($division->name) ?></h2>
	<dl class="row">
		<dt class="col-sm-2 text-end"><?= __('League') ?></dt>
		<dd class="col-sm-10 mb-0"><?php
			echo $this->element('Leagues/block', ['league' => $division->league]);
			echo $this->Html->iconLink('view_24.png', ['controller' => 'Leagues', 'action' => 'view', '?' => ['league' => $division->league_id]], ['id' => 'LeagueDetailsIcon']);
			$this->Html->scriptBlock('zjQuery("#LeagueDetailsIcon").bind("click", function (event) { zjQuery("#LeagueDetails").toggle(); return false; });', ['buffer' => true]);
		?></dd>
		<fieldset id="LeagueDetails" style="display:none;">
			<legend><?= __('League Details') ?></legend>
			<dl class="row">
				<dt class="col-sm-2 text-end"><?= __('Season') ?></dt>
				<dd class="col-sm-10 mb-0"><?= __($division->league->season) ?></dd>
<?php
if ($this->Authorize->can('edit', $division)):
	if ($division->league->hasSpirit()):
?>
				<dt class="col-sm-2 text-end"><?= __('Spirit Questionnaire') ?></dt>
				<dd class="col-sm-10 mb-0"><?= __(Configure::read("options.spirit_questions.{$division->league->sotg_questions}")) ?></dd>
				<dt class="col-sm-2 text-end"><?= __('Spirit Numeric Entry') ?></dt>
				<dd class="col-sm-10 mb-0"><?= $division->league->numeric_sotg ? __('Yes') : __('No') ?></dd>
				<dt class="col-sm-2 text-end"><?= __('Spirit Display') ?></dt>
				<dd class="col-sm-10 mb-0"><?= __(Inflector::Humanize($division->league->display_sotg)) ?></dd>
<?php
	endif;
?>
				<dt class="col-sm-2 text-end"><?= __('Expected Max Score') ?></dt>
				<dd class="col-sm-10 mb-0"><?= $division->league->expected_max_score ?></dd>
<?php
endif;
?>
			</dl>
		</fieldset>
		<?= $this->element('Divisions/details', [
			'division' => $division,
			'people' => $division->people,
		]) ?>
	</dl>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?= $this->element('Divisions/actions', [
	'league' => $division->league,
	'division' => $division,
	'format' => 'list',
]) ?>
	</ul>
</div>
<?php
if (!empty($division->footer)):
?>
<div class="division_footer"><?= $division->footer ?></div>
<?php
endif;

echo $this->element('Divisions/teams', [
	'league' => $division->league,
	'division' => $division,
	'teams' => $division->teams,
]);
echo $this->element('Divisions/register', ['events' => $division->events]);

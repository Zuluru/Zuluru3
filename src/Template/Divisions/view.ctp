<?php
use Cake\Core\Configure;
use Cake\Utility\Inflector;

$this->Html->addCrumb(__('Divisions'));
$this->Html->addCrumb(h($division->full_league_name));
$this->Html->addCrumb(__('View'));
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
	<dl class="dl-horizontal">
		<dt><?= __('League') ?></dt>
		<dd><?php
			echo $this->element('Leagues/block', ['league' => $division->league]);
			echo $this->Html->iconLink('view_24.png', ['controller' => 'Leagues', 'action' => 'view', 'league' => $division->league_id], ['id' => 'LeagueDetailsIcon']);
			$this->Html->scriptBlock('jQuery("#LeagueDetailsIcon").bind("click", function (event) { jQuery("#LeagueDetails").toggle(); return false; });', ['buffer' => true]);
		?></dd>
		<fieldset id="LeagueDetails" style="display:none;">
			<legend><?= __('League Details') ?></legend>
			<dl class="dl-horizontal">
				<dt><?= __('Season') ?></dt>
				<dd><?= __($division->league->season) ?></dd>
<?php
if ($this->Authorize->can('edit', $division)):
	if ($division->league->hasSpirit()):
?>
				<dt><?= __('Spirit Questionnaire') ?></dt>
				<dd><?= __(Configure::read("options.spirit_questions.{$division->league->sotg_questions}")) ?></dd>
				<dt><?= __('Spirit Numeric Entry') ?></dt>
				<dd><?= $division->league->numeric_sotg ? __('Yes') : __('No') ?></dd>
				<dt><?= __('Spirit Display') ?></dt>
				<dd><?= __(Inflector::Humanize($division->league->display_sotg)) ?></dd>
<?php
	endif;
?>
				<dt><?= __('Expected Max Score') ?></dt>
				<dd><?= $division->league->expected_max_score ?></dd>
<?php
endif;
?>
			</dl>
		</fieldset>
		<?= $this->element('Divisions/details', array_merge([
			'division' => $division,
			'people' => $division->people,
		], compact('i', 'class'))) ?>
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

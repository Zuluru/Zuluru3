<?php
use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\Utility\Inflector;
?>

<div class="score-box" id="score_team_<?= $team['id'] ?>">
	<table>
		<tbody>
			<tr>
				<td class="actions down" rowspan="2"><?= $this->Html->link('&ndash;', '#', ['escape' => false]) ?></td>
				<td class="team-name" colspan="2"><?= $team['name'] ?></td>
				<td class="actions up" rowspan="2"><?= $this->Html->link('+', '#') ?></td>
			</tr>
			<tr><td class="score" colspan="2"><?= $score ?></td></tr>
			<tr>
				<td class="actions timeout" colspan="2"><?= $this->Html->link(__('Timeout'), '#') ?> (<span class="timeout_count"><?= __('{0} taken', $timeouts) ?></span>)</td>
				<td class="actions other" colspan="2"><?= $this->Html->link('Other', '#') ?></td>
			</tr>
		</tbody>
	</table>
</div>
<?php
$url_up = ['controller' => 'Games', 'action' => 'score_up', 'game' => $game['Game']['id'], 'team' => $submitter];
$url_down = ['controller' => 'Games', 'action' => 'score_down', 'game' => $game['Game']['id'], 'team' => $submitter];
$url_timeout = ['controller' => 'Games', 'action' => 'timeout', 'game' => $game['Game']['id'], 'team' => $submitter];
$url_other = ['controller' => 'Games', 'action' => 'play', 'game' => $game['Game']['id'], 'team' => $submitter];
$score_options = Configure::read("sports.{$game->division->league->sport}.score_options");
$other_options = Configure::read("sports.{$game->division->league->sport}.other_options");
$spinner = $this->Html->iconImg('spinner.gif');
$submit = __('Submit');
$cancel = __('Cancel');

if (($has_stats && ($submitter == $team['id'] || $submitter === null)) || count($score_options) > 1):
?>
<div id="ScoreDetails<?= $team['id'] ?>" title="Scoring Play Details" class="form">
<div class="zuluru">
<?php
echo $this->Form->create(false, [
		'id' => "ScoreForm{$team['id']}",
		'url' => $url_up,
]);

echo $this->Form->hidden('team_id', ['value' => $team['id']]);
echo $this->Form->hidden('score_from');
echo $this->Form->input('play', [
		'options' => \App\Config\make_options(array_keys($score_options)),
		'empty' => '---',
		'hide_single' => true,
]);
echo $this->Form->input('created', [
		'type' => 'datetime',
		'label' => __('Time'),
]);

if ($has_stats) {
	// Build the roster options
	$roster = [];
	$has_numbers = Configure::read('feature.shirt_numbers') && $team->has('people') && collection($team->people)->some(function ($person) {
		return $person->_joinData->number != null;
	});
	foreach ($team['Person'] as $person) {
		$option = $person['full_name'];
		if ($has_numbers && $person['TeamsPerson']['number'] !== null && $person['TeamsPerson']['number'] !== '') {
			$option = "{$person['TeamsPerson']['number']} $option";
			if ($person['TeamsPerson']['number'] < 10) {
				$option = " $option";
			}
		}
		$roster[$person['id']] = $option;
	}
	asort($roster);

	foreach($game['Division']['League']['StatType'] as $stat) {
		echo $this->Form->input("Stat.{$stat['id']}", [
				'label' => __(Inflector::singularize($stat['name'])),
				'options' => $roster,
				'empty' => '---',
		]);
	}
}

	echo $this->Form->end();
?>
</div>
</div>
<?php
	$this->Html->scriptBlock("
		jQuery('#ScoreDetails{$team['id']}').dialog({
			autoOpen: false,
			buttons: {
				'$submit': function () {
					jQuery(this).dialog('close');
					jQuery('#ScoreForm{$team['id']}').find('#score_from').val(jQuery('#score_team_{$team['id']} td.score').html());
					jQuery('#ScoreForm{$team['id']}').ajaxSubmit({
						type: 'POST',
						target: '#temp_update',
						error: function (message, status, error){
							alert('Error ' + status + ': ' + message.statusText);
						}
					});
					// Reset the form for the next time
					jQuery('#ScoreForm{$team['id']}').each(function(){
						this.reset();
					});
				},
				'$cancel': function () { jQuery(this).dialog('close'); }
			},
			modal: true,
			resizable: false,
			width: 500
		});
	", ['buffer' => true]);
	$this->Html->scriptBlock("jQuery('#score_team_{$team['id']}').find('td.up a').bind('click', function (event) { openDialog('#ScoreDetails{$team['id']}'); return false; });", ['buffer' => true]);
else:
	$url_up = Router::url($url_up);
	$play = current(array_keys($score_options));
	// TODOLATER: Deal with all usages of temp_update
	$this->Html->scriptBlock("jQuery('#score_team_{$team['id']}').find('td.up a').bind('click', function (event) {
		var score_from = jQuery('#score_team_{$team['id']}').find('td.score').html();
		jQuery('#score_team_{$team['id']}').find('td.score').html('$spinner');
		jQuery.ajax({
			type: 'POST',
			data: {
				'data[team_id]': {$team['id']},
				'data[score_from]': score_from,
				'data[play]': '$play'
			},
			success: function (data, textStatus) {
				jQuery('#temp_update').html(data.content);
			},
			url: '$url_up'
		});
		return false;
	});", ['buffer' => true]);
endif;

$url_down = Router::url($url_down);
$this->Html->scriptBlock("jQuery('#score_team_{$team['id']}').find('td.down a').bind('click', function (event) {
	var score_from = jQuery('#score_team_{$team['id']}').find('td.score').html();
	jQuery('#score_team_{$team['id']}').find('td.score').html('$spinner');
	jQuery.ajax({
		type: 'POST',
		data: {
			'data[team_id]': {$team['id']},
			'data[score_from]': score_from
		},
		success: function (data, textStatus) {
			jQuery('#temp_update').html(data.content);
		},
		url: '$url_down'
	});
	return false;
});", ['buffer' => true]);

$url_timeout = Router::url($url_timeout);
$this->Html->scriptBlock("jQuery('#score_team_{$team['id']}').find('td.timeout').find('a').bind('click', function (event) {
	if (confirm('Timeout called?')) {
		jQuery.ajax({
			type: 'POST',
			data: {
				'data[team_id]': {$team['id']},
				'data[score_from]': jQuery('#score_team_{$team['id']}').find('td.score').html(),
			},
			success: function (data, textStatus) {
				jQuery('#temp_update').html(data.content);
			},
			url: '$url_timeout'
		});
	}
	return false;
});", ['buffer' => true]);

if (count($other_options) > 1):
?>
<div id="OtherDetails<?= $team['id'] ?>" title="<?= __('Other Details') ?>" class="form">
<div class="zuluru">
<?php
	echo $this->Form->create(false, [
		'id' => "OtherForm{$team['id']}",
		'url' => $url_other,
	]);

	echo $this->Form->hidden('team_id', ['value' => $team['id']]);
	echo $this->Form->hidden('score_from');
	// TODO: Add in non-scoring stats that are being tracked for this division
	echo $this->Form->input('play', [
			'options' => $other_options,
			'empty' => '---',
	]);
	echo $this->Form->input('created', [
			'type' => 'datetime',
			'label' => __('Time'),
	]);
	echo $this->Form->end();
?>
</div>
</div>

<?php
	$url_other = Router::url($url_other);
	// TODOLATER: What's with the doubled # selector below?
	$this->Html->scriptBlock("
		jQuery('#OtherDetails{$team['id']}').dialog({
			autoOpen: false,
			buttons: {
				'$submit': function () {
					jQuery(this).dialog('close');
					jQuery('#OtherForm{$team['id']} #score_from').val(jQuery('#score_team_{$team['id']} td.score').html());
					jQuery('#OtherForm{$team['id']}').ajaxSubmit({
						type: 'POST',
						target: '#temp_update',
						error: function (message, status, error){
							alert('Error ' + status + ': ' + message.statusText);
						}
					});
					// Reset the form for the next time
					jQuery('#OtherForm{$team['id']}').each(function(){
						this.reset();
					});
				},
				'$cancel': function () { jQuery(this).dialog('close'); }
			},
			modal: true,
			resizable: false,
			width: 500
		});
	", ['buffer' => true]);
	$this->Html->scriptBlock("jQuery('#score_team_{$team['id']}').find('td.other').find('a').bind('click', function (event) { openDialog('#OtherDetails{$team['id']}'); return false; });");
endif;

$this->Html->scriptBlock("
function openDialog(id) {
	var d = new Date();
	var h = d.getHours();
	var m = d.getMinutes();
	var mer = 'am';
	if (h >= 12) {
		mer = 'pm';
	}
	if (h == 0) {
		h = 12;
	} else if (h > 12) {
		h = h - 12;
	}
	if (h < 10) {
		h = '0' + h;
	}
	if (m < 10) {
		m = '0' + m;
	}
	jQuery(id + ' #createdHour').val(h);
	jQuery(id + ' #createdMin').val(m);
	jQuery(id + ' #createdMeridian').val(mer);
	jQuery(id).dialog('open');
}
", ['buffer' => true]);

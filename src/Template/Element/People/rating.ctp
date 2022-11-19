<?php
use Cake\Core\Configure;
?>

<div id="rating_dialog_<?= $sport ?>" class="form" title="<?= __('{0} Player Rating', ZULURU) ?>">
<?php
$questions = Configure::read("sports.{$sport}.rating_questions");
?>

<div class="zuluru">
<p><?= __('Fill out this questionnaire and then click "Calculate" below to figure out the skill level you should use in {0}.', ZULURU) ?></p>
<p><?= __('The questionnaire is divided into {0} sections. Answer each as honestly as possible, and the resulting {1} rating should be fairly accurate. When answering questions regarding relative skills, compare yourself to the average of all people playing the sport, not only those that you regularly compete against.', implode(' and ', array_keys($questions)), ZULURU) ?></p>
<p><?= __('The calculated value will be entered on the {0} profile editing form.', ZULURU) ?></p>

<form name="rating_<?= $sport ?>">

<?php
$i = 1;
$min = $max = 0;
foreach ($questions as $group_label => $group_questions) {
	echo $this->Html->tag('h2', $group_label . ' ' . __('Questions') . ':') . "\n";
	foreach ($group_questions as $label => $options) {
		$min += min(array_keys($options));
		$max += max(array_keys($options));
		echo $this->Form->input("{$sport}_q{$i}", [
			'label' => "$i. $label",
			'type' => 'radio',
			'options' => $options,
			'hiddenField' => false,
		]) . "\n";
		++ $i;
	}
}
?>

</form>
</div>
</div>

<?php
$calculate = __('Calculate');
$cancel = __('Cancel');
$this->Html->scriptBlock("
zjQuery('#rating_dialog_$sport').dialog({
	autoOpen: false,
	buttons: {
		'$calculate': function () {
			if (calculate_rating('$sport', $min, $max)) {
				zjQuery('#rating_dialog_$sport').dialog('close');
			}
		},
		'$cancel': function () {
			zjQuery('#rating_dialog_$sport').dialog('close');
		}
	},
	modal: true,
	resizable: false,
	width: 640,
	height: 480
});
", ['buffer' => true]);

if (!Configure::read('skill_rating_functions_added')) {
	Configure::write('skill_rating_functions_added', true);
	$answer_all = __('You must answer all questions.');
	echo $this->Html->scriptBlock("
// function to calculate the rating
function calculate_rating(sport, min, max) {
	var sum = 0;

	// Check for skipped questions and show error
	var okay = true;
	zjQuery('form[name=rating_' + sport + ']').find('div.form-group.radio').each(function() {
		if (zjQuery(this).find('input:checked').size() == 0) {
			zjQuery(this).addClass('error');
			okay = false;
		} else {
			zjQuery(this).removeClass('error');
		}
	});
	if (!okay) {
		alert('$answer_all');
		return false;
	}

	// Sum up all selected answers
	zjQuery('form[name=rating_' + sport + ']').find('input:checked').each(function() {
		sum += parseInt(zjQuery(this).val());
	});

	// Move the sum so the average is zero
	sum -= (max+min)/2;

	// Scale the result to a rating between 0.5 and 9.5
	var rating = 9/(max-min) * sum + 5;

	// Round to an integer (1 to 10)
	rating = Math.round(rating);

	// put the result into the text box
	zjQuery(zjQuery('#rating_dialog_' + sport).data('field')).val(rating);
	return true;
}

function dorating(sport, field) {
	zjQuery('#rating_dialog_' + sport).data('field', field);
	zjQuery('#rating_dialog_' + sport).dialog('open');
}
	");
}

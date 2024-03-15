<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\Utility\Inflector;
use App\Controller\AppController;

$identity = $this->Authorize->getIdentity();
if (!$identity || !$identity->isPlayer()) {
	return;
}

foreach ($fields as $key => $field) {
	// TODO: Centralize checking of profile fields
	$access = Configure::read("profile.$field");
	if (!$access || $access == PROFILE_REGISTRATION) {
		unset($fields[$key]);
	}
}
if (empty($fields)) {
	return;
}

$person = $identity->getOriginalData()->person;
if ($person->modified) {
	if ($identity->isChild()) {
		$check = $person->modified->addMonths(6);
	} else {
		$check = $person->modified->addYear();
	}
	if ($check->isFuture()) {
		return;
	}
}
$skills = $this->UserCache->read('Skills');
$sports = Configure::read('options.sport');
?>
<div id="ProfileConfirmation" title="<?= __('Confirmation') ?>" style="display:none;">
	<div class="zuluru">
<?php
if (empty($person->user_id)) {
	$for = __('profile information for {0}', $person->first_name);
} else {
	$for = __('your profile information');
}
echo $this->Html->para(null, __('It\'s been a while since you\'ve confirmed {0}. It is important that we have accurate information for team-building and/or evaluation.', $for));
echo $this->Html->para(null, __('Please either update or confirm the information below.'));
?>

		<dl class="dl-horizontal narrow">
<?php
foreach ($fields as $field):
?>

			<dt><?= __(Inflector::humanize($field)) ?>:</dt>
			<dd><?php
			switch ($field) {
				case 'height':
					echo $person->$field . ' ' . (Configure::read('feature.units') == 'Metric' ? __('cm') : __('inches'));
					break;

				case 'skill_level':
				case 'year_started':
					$lines = [];
					foreach ($skills as $skill) {
						if ($skill->enabled) {
							$line = $skill->$field;
							if (count($sports) > 1) {
								$line = Inflector::humanize($skill->sport) . ': ' . $line;
							}
							$lines[] = $line;
						}
					}
					echo implode('<br/>', $lines);
					break;

				default:
					echo $person->$field;
			}
			?>&nbsp;</dd>
<?php
endforeach;
?>
		</dl>
	</div>
</div>
<?php
$edit = __('Update profile');
$edit_link = Router::url(['controller' => 'People', 'action' => 'edit', 'return' => AppController::_return()]);
$confirm = __('Confirm');
$confirm_link = Router::url(['controller' => 'People', 'action' => 'confirm']);
$this->Html->scriptBlock("
zjQuery('#ProfileConfirmation').dialog({
	buttons: {
		'$edit': function () {
			window.location.href = '$edit_link';
		},
		'$confirm': function () {
			zjQuery('#ProfileConfirmation').dialog('close');
			zjQuery.ajax({
				type: 'GET',
				url: '$confirm_link'
			})
				.done(function (response) {
					if (response._message) {
						alert(response._message[0].message);
					}
				});
		}
	},
	width: 400,
	modal: true
});
", ['buffer' => true]);

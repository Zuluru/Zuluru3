<?php
/**
 * @type $this \App\View\AppView
 * @type $events \App\Model\Entity\Event[]
 * @type $affiliates int[]
 * @type $step string
 */

$this->Html->addCrumb(__('Registration Events'));
$this->Html->addCrumb(__('Wizard'));
?>

<div class="events index">
<h2><?= __('Registration Wizard') ?></h2>
<?php
echo $this->Html->para('highlight-message', __('This wizard walks you through registration options based on your current status. As you register for things, different options may appear here. You might also want to review our {0}.',
	$this->Html->link(__('complete list of offerings'), ['action' => 'index'])));
echo $this->element('Registrations/relative_notice');

echo $this->element('Registrations/notice');

if ($step) {
	echo $this->element('Events/selectors', compact('events'));
}

$events = collection($events)->groupBy('affiliate_id')->toArray();
foreach ($events as $affiliate_id => $affiliate_events):
	if (count($affiliates) > 1):
?>
	<h3 class="affiliate" style="clear:both;"><?= h($affiliate_events[0]->affiliate->name) ?></h3>
<?php
	endif;

	$events_by_type = collection($affiliate_events)->groupBy(function ($event) { return $event->event_type_id; })->toArray();
	$event_types_available = array_keys($events_by_type);
	if (empty($step) && count($event_types_available) == 1) {
		$step = current($event_types_available);
	}

	// TODO: String steps are from URLs, numeric are when there's only one type available
	switch ($step) {
		case 'membership':
		case 1:
			if (!empty($events_by_type[1])) {
				echo $this->Html->tag('h3', __($events_by_type[1][0]->event_type->name));
				echo $this->Html->para(null, __('You are currently eligible for the following memberships.'));
				echo $this->element('Events/list', ['events' => $events_by_type[1]]);
			}
			break;

		case 'league_team':
		case 2:
			if (!empty($events_by_type[2])) {
				echo $this->Html->tag('h3', __($events_by_type[2][0]->event_type->name));
				echo $this->Html->para(null, __('You are currently eligible to register a team in the following leagues.'));
				echo $this->element('Events/list', ['events' => $events_by_type[2]]);
			}
			break;

		case 'league_individual':
		case 3:
			if (!empty($events_by_type[3])) {
				echo $this->Html->tag('h3', __($events_by_type[3][0]->event_type->name));
				echo $this->Html->para(null, __('You are currently eligible to register as an individual in the following leagues.'));
				echo $this->element('Events/list', ['events' => $events_by_type[3]]);
			}
			break;

		case 'league_youth':
		case 8:
			if (!empty($events_by_type[8])) {
				echo $this->Html->tag('h3', __($events_by_type[8][0]->event_type->name));
				echo $this->Html->para(null, __('You are currently eligible to register as a youth in the following leagues.'));
				echo $this->element('Events/list', ['events' => $events_by_type[8]]);
			}
			break;

		case 'event_team':
		case 4:
			if (!empty($events_by_type[4])) {
				echo $this->Html->tag('h3', __($events_by_type[4][0]->event_type->name));
				echo $this->Html->para(null, __('You are currently eligible to register a team for the following events.'));
				echo $this->element('Events/list', ['events' => $events_by_type[4]]);
			}
			break;

		case 'event_individual':
		case 5:
			if (!empty($events_by_type[5])) {
				echo $this->Html->tag('h3', __($events_by_type[5][0]->event_type->name));
				echo $this->Html->para(null, __('You are currently eligible to register as an individual for the following events.'));
				echo $this->element('Events/list', ['events' => $events_by_type[5]]);
			}
			break;

		case 'clinic':
		case 6:
			if (!empty($events_by_type[6])) {
				echo $this->Html->tag('h3', __($events_by_type[6][0]->event_type->name));
				echo $this->Html->para(null, __('You are currently eligible to register for the following clinics.'));
				echo $this->element('Events/list', ['events' => $events_by_type[6]]);
			}
			break;

		case 'social_event':
		case 7:
			if (!empty($events_by_type[7])) {
				echo $this->Html->tag('h3', __($events_by_type[7][0]->event_type->name));
				echo $this->Html->para(null, __('You can register for the following social events.'));
				echo $this->element('Events/list', ['events' => $events_by_type[7]]);
			}
			break;

		default:
			if (!empty($events_by_type[1])) {
				echo $this->Html->para(null, __('You are eligible to {0}. A membership is typically required before you can sign up for team-related events.', $this->Html->link(__('register for membership in the club'), ['action' => 'wizard', 'membership'])));
				echo $this->Html->tag('span',
						$this->Html->link('Register for membership', ['action' => 'wizard', 'membership']),
						['class' => 'actions']);
			}

			if (!empty($events_by_type[2])) {
				echo $this->Html->para(null, __('You are eligible to {0}. This is for team coaches or captains looking to add their team for the upcoming season.', $this->Html->link(__('register a league team'), ['action' => 'wizard', 'league_team'])));
				echo $this->Html->tag('span',
						$this->Html->link('Register a league team', ['action' => 'wizard', 'league_team']),
						['class' => 'actions']);
			}

			if (!empty($events_by_type[3])) {
				echo $this->Html->para(null, __('You are eligible to {0}. This is for individuals who do not already have a team and want to play on a "hat team".', $this->Html->link(__('register as an individual for league play'), ['action' => 'wizard', 'league_individual'])));
				echo $this->Html->tag('span',
						$this->Html->link('Register as an individual', ['action' => 'wizard', 'league_individual']),
						['class' => 'actions']);
			}

			if (!empty($events_by_type[8])) {
				echo $this->Html->para(null, __('You are eligible to {0}. This is for youth who do not already have a team and want to play on a "hat team".', $this->Html->link(__('register as a youth for league play'), ['action' => 'wizard', 'league_youth'])));
				echo $this->Html->tag('span',
						$this->Html->link('Register as a youth', ['action' => 'wizard', 'league_youth']),
						['class' => 'actions']);
			}

			if (!empty($events_by_type[4])) {
				echo $this->Html->para(null, __('You are eligible to {0}. This is for team coaches or captains looking to add their team for a tournament or similar event.', $this->Html->link(__('register a team for a one-time event'), ['action' => 'wizard', 'event_team'])));
				echo $this->Html->tag('span',
						$this->Html->link('Register a team for an event', ['action' => 'wizard', 'event_team']),
						['class' => 'actions']);
			}

			if (!empty($events_by_type[5])) {
				echo $this->Html->para(null, __('You are eligible to {0}. This is for individuals who do not already have a team and want to play on a "hat team" in a tournament or similar event.', $this->Html->link(__('register as an individual for a one-time event'), ['action' => 'wizard', 'event_individual'])));
				echo $this->Html->tag('span',
						$this->Html->link('Register as an individual', ['action' => 'wizard', 'event_individual']),
						['class' => 'actions']);
			}

			if (!empty($events_by_type[6])) {
				echo $this->Html->para(null, __('There are {0} that you might be interested in.', $this->Html->link(__('upcoming clinics'), ['action' => 'wizard', 'clinic'])));
				echo $this->Html->tag('span',
						$this->Html->link('Register for a clinic', ['action' => 'wizard', 'clinic']),
						['class' => 'actions']);
			}

			if (!empty($events_by_type[7])) {
				echo $this->Html->para(null, __('There are {0} that you might be interested in.', $this->Html->link(__('upcoming social events'), ['action' => 'wizard', 'social_event'])));
				echo $this->Html->tag('span',
						$this->Html->link('Register for a social event', ['action' => 'wizard', 'social_event']),
						['class' => 'actions']);
			}

			break;
	}

endforeach;
?>
</div>
<?php
echo $this->element('People/confirmation', ['fields' => ['height', 'shirt_size', 'year_started', 'skill_level']]);
echo $this->element('Events/category_scaffolding');

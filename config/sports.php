<?php
/**
 * Configuration details for all the supported sports.
 */
namespace App\Config;

use Cake\Utility\Inflector;

$court = 'court';
$diamond = 'diamond';
$field = 'field';
$gym = 'gym';
$pitch = 'pitch';
$rink = 'rink';

$config['sports'] = [
	'baseball' => [
		'field' => $diamond,
		'field_cap' => Inflector::humanize($diamond),
		'fields' => Inflector::pluralize($diamond),
		'fields_cap' => Inflector::humanize(Inflector::pluralize($diamond)),

		'start' => [
			'stat_sheet' => null,
			'stat_sheet_direction' => false,
			'live_score' => null,
			'box_score' => null,
			'twitter' => '{0} batting',
		],

		'roster_requirements' => [
			'womens' => 12,
			'mens' => 12,
			'co-ed' => 12,
		],

		'positions' => [
			'unspecified' => 'Unspecified',
			'pitcher' => 'Pitcher',
			'catcher' => 'Catcher',
			'firstbase' => 'First Base',
			'secondbase' => 'Second Base',
			'shortstop' => 'Shortstop',
			'thirdbase' => 'Third Base',
			'rightfielder' => 'Right Fielder',
			'centerfielder' => 'Center Fielder',
			'leftfielder' => 'Left Fielder',
			'utilityinfielder' => 'Utility Infielder',
			'utilityoutfielder' => 'Utility Outfielder',
			'designatedhitter' => 'Designated Hitter',
		],

		'score_options' => [
			'Run' => 1,
		],

		'other_options' => [
			'Pitcher' => 'Pitching change',
			'Fielder' => 'Fielding change',
			'Batter' => 'Pinch hitter',
			'Runner' => 'Pinch runner',
		],

		'rating_questions' => false,
	],

	'basketball' => [
		'field' => $court,
		'field_cap' => Inflector::humanize($court),
		'fields' => Inflector::pluralize($court),
		'fields_cap' => Inflector::humanize(Inflector::pluralize($court)),

		'start' => [
			'stat_sheet' => null,
			'stat_sheet_direction' => true,
			'live_score' => null,
			'box_score' => null,
			'twitter' => null,
		],

		'roster_requirements' => [
			'womens' => 8,
			'mens' => 8,
			'co-ed' => 8,
		],

		'positions' => [
			'unspecified' => 'Unspecified',
			'Guard' => 'Guard',
			'Forward' => 'Forward',
			'Center' => 'Center',
			'Point Guard' => 'Point Guard',
			'Shooting Guard' => 'Shooting Guard',
			'Small Forward' => 'Small Forward',
			'Power Forward' => 'Power Forward',
		],

		'score_options' => [
			'Field goal' => 2,
			'3 pointer' => 3,
			'Free throw' => 1,
		],

		'other_options' => [
			// TODO
		],

		'rating_questions' => false,
	],

	'cricket' => [
		'field' => $pitch,
		'field_cap' => Inflector::humanize($pitch),
		'fields' => Inflector::pluralize($pitch),
		'fields_cap' => Inflector::humanize(Inflector::pluralize($pitch)),

		'start' => [
			'stat_sheet' => 'First bats',
			'stat_sheet_direction' => false,
			'live_score' => 'Batting team',
			'box_score' => '{0} batting',
			'twitter' => '{0} batting',
		],

		'roster_requirements' => [
			'womens' => 16,
			'mens' => 16,
			'co-ed' => 16,
		],

		'positions' => [
			'unspecified' => 'Unspecified',
			'bowler' => 'Bowler',
			'batter' => 'Batter',
			'wicketkeeper' => 'Wicketkeeper',
			'allrounder' => 'All Rounder',
		],

		'score_options' => [
			// TODO
		],

		'other_options' => [
			// TODO
		],

		'rating_questions' => false,
	],

	'crossfit' => [
		'field' => $gym,
		'field_cap' => Inflector::humanize($gym),
		'fields' => Inflector::pluralize($gym),
		'fields_cap' => Inflector::humanize(Inflector::pluralize($gym)),

		'start' => [
			'stat_sheet' => null,
			'stat_sheet_direction' => false,
			'live_score' => null,
			'box_score' => null,
			'twitter' => null,
		],

		'roster_requirements' => [
			'open' => 1,
		],

		'positions' => [
		],

		'score_options' => [
			// TODO
		],

		'other_options' => [
			// TODO
		],

		'rating_questions' => false,

		'competition' => true,
	],

	'dodgeball' => [
		'field' => $court,
		'field_cap' => Inflector::humanize($court),
		'fields' => Inflector::pluralize($court),
		'fields_cap' => Inflector::humanize(Inflector::pluralize($court)),

		'start' => [
			'stat_sheet' => null,
			'stat_sheet_direction' => true,
			'live_score' => null,
			'box_score' => null,
			'twitter' => null,
		],

		'roster_requirements' => [
			'6 (min 2 women)'       => 6,
		],

		'positions' => [
		],

		'score_options' => [
			// TODO
		],

		'other_options' => [
			// TODO
		],

		'rating_questions' => false,
	],

	'football' => [
		'field' => $field,
		'field_cap' => Inflector::humanize($field),
		'fields' => Inflector::pluralize($field),
		'fields_cap' => Inflector::humanize(Inflector::pluralize($field)),

		'start' => [
			'stat_sheet' => 'Kick-off received by',
			'stat_sheet_direction' => true,
			'live_score' => 'Receiving team',
			'box_score' => '{0} received the kick-off',
			'twitter' => '{1} kicks off to {0}',
		],

		'roster_requirements' => [
			'3/3' => 10,
			'4/2' => 10,
			'womens 6s' => 10,
			'mens 6s' => 10,
			'co-ed 6s' => 10,
			'womens 11s' => 16,
			'mens 11s' => 16,
			'co-ed 11s' => 16,
			'womens 12s' => 18,
			'mens 12s' => 18,
			'co-ed 12s' => 18,
		],

		'positions' => [
			'unspecified' => 'Unspecified',
			'quarterback' => 'Quarterback',
			'center' => 'Center',
			'tackle' => 'Tackle',
			'guard' => 'Guard',
			'tightend' => 'Tight End',
			'halfback' => 'Halfback',
			'fullback' => 'Fullback',
			'runningback' => 'Running Back',
			'widereceiver' => 'Wide Receiver',
			'linebacker' => 'Linebacker',
			'middlelinebacker' => 'Middle Linebacker',
			'outsidelinebacker' => 'Outside Linebacker',
			'end' => 'End',
			'cornerback' => 'Cornerback',
			'safety' => 'Safety',
		],

		'score_options' => [
			'Touchdown' => 6,
			'Conversion' => 1,
			'Two-point conversion' => 2,
			'Field goal' => 3,
			'Safety' => 2,
			'Single' => 1,
			'Rouge' => 1,
		],

		'other_options' => [
			// TODO
		],

		'rating_questions' => false,
	],

	'hockey' => [
		'field' => $rink,
		'field_cap' => Inflector::humanize($rink),
		'fields' => Inflector::pluralize($rink),
		'fields_cap' => Inflector::humanize(Inflector::pluralize($rink)),

		'start' => [
			'stat_sheet' => null,
			'stat_sheet_direction' => true,
			'live_score' => null,
			'box_score' => null,
			'twitter' => null,
		],

		'roster_requirements' => [
			'womens' => 10,
			'mens' => 10,
			'co-ed' => 10,
		],

		'positions' => [
			'unspecified' => 'Unspecified',
			'goalie' => 'Goalie',
			'defence' => 'Defence',
			'forward' => 'Forward',
			'leftwinger' => 'Left Winger',
			'center' => 'Center',
			'rightwinger' => 'Right Winger',
		],

		'score_options' => [
			'Goal' => 1,
		],

		'other_options' => [
			// TODO
		],

		'rating_questions' => false,
	],

	'rugby' => [
		'field' => $pitch,
		'field_cap' => Inflector::humanize($pitch),
		'fields' => Inflector::pluralize($pitch),
		'fields_cap' => Inflector::humanize(Inflector::pluralize($pitch)),

		'start' => [
			'stat_sheet' => null,
			'stat_sheet_direction' => true,
			'live_score' => null,
			'box_score' => null,
			'twitter' => null,
		],

		'roster_requirements' => [
			'womens' => 18,
			'mens' => 18,
			'co-ed' => 18,
			'womens sevens' => 10,
			'mens sevens' => 10,
			'co-ed sevens' => 10,
			'womens tens' => 13,
			'mens tens' => 13,
			'co-ed tens' => 13,
		],

		'positions' => [
			'unspecified' => 'Unspecified',
			'prop' => 'Prop',
			'looseheadprop' => 'Loosehead Prop',
			'hooker' => 'Hooker',
			'tightheadprop' => 'Tighthead Prop',
			'secondrower' => 'Second Rower',
			'blindsideflanker' => 'Blindside Flanker',
			'opensideflanker' => 'Openside Flanker',
			'number8' => 'Number 8',
			'scrumhalf' => 'Scrumhalf',
			'flyhalf' => 'Flyhalf',
			'winger' => 'Winger',
			'center' => 'Center',
			'weaksidewinger' => 'Weak Side Winger',
			'insidecenter' => 'Inside Center',
			'outsidecenter' => 'Outside Center',
			'strongsidewinger' => 'Strong Side Winger',
			'fullback' => 'Fullback',
		],

		'score_options' => [
			// TODO
		],

		'other_options' => [
			// TODO
		],

		'rating_questions' => false,
	],

	'soccer' => [
		'field' => $pitch,
		'field_cap' => Inflector::humanize($pitch),
		'fields' => Inflector::pluralize($pitch),
		'fields_cap' => Inflector::humanize(Inflector::pluralize($pitch)),

		'start' => [
			'stat_sheet' => 'Initial kick-off',
			'stat_sheet_direction' => true,
			'live_score' => 'Team taking kick-off',
			'box_score' => '{0} took the kick-off',
			'twitter' => '{0} takes the kick-off',
		],

		'roster_requirements' => [
			'womens' => 16,
			'mens' => 16,
			'co-ed' => 16,
		],

		'positions' => [
			'unspecified' => 'Unspecified',
			'goalkeeper' => 'Goalkeeper',
			'fullback' => 'Fullback',
			'midfielder' => 'Midfielder',
			'attacker' => 'Attacker',
			'sweeper' => 'Sweeper',
			'centerfullback' => 'Center Fullback',
			'leftfullback' => 'Left Fullback',
			'rightfullback' => 'Right Fullback',
			'leftwingback' => 'Left Wingback',
			'wingback' => 'Wingback',
			'rightwingback' => 'Right Wingback',
			'leftmidfielder' => 'Left Midfielder',
			'defensivemidfielder' => 'Defensive Midfielder',
			'attackingmidfielder' => 'Attacking Midfielder',
			'rightmidfielder' => 'Right Midfielder',
			'leftwinger' => 'Left Winger',
			'striker' => 'Striker',
			'secondstriker' => 'Second Striker',
			'centerforward' => 'Center Forward',
			'rightwinger' => 'Right Winger',
		],

		'score_options' => [
			'Goal' => 1,
		],

		'other_options' => [
			'Half' => 'Kick-off to start second half',
			'Substitution' => 'Substitution',
		],

		'rating_questions' => false,
	],

	'ultimate' => [
		'field' => $field,
		'field_cap' => Inflector::humanize($field),
		'fields' => Inflector::pluralize($field),
		'fields_cap' => Inflector::humanize(Inflector::pluralize($field)),

		'start' => [
			'stat_sheet' => 'Initial pull',
			'stat_sheet_direction' => true,
			'live_score' => 'Pulling team',
			'box_score' => '{0} pulled',
			'twitter' => '{1} pulls to {0}',
		],

		'roster_requirements' => [
			'4/3' => 12,
			'5/2' => 12,
			'3/3' => 10,
			'4/2' => 10,
			'3/2' => 8,
			'2/2' => 7,
			'womens' => 12,
			'mens' => 12,
			'open' => 12,
		],

		'gender_ratio' => [
			'4/3' => [
				'all 3W/4O' => __('All points played 3W/4O'),
				'some 4W/3O' => __('Some points played 4W/3O'),
				'half 4W/3O' => __('4W/3O about half the time'),
				'mostly 4W/3O' => __('Most points played 4W/3O'),
				'all 4W/3O' => __('All points played 4W/3O'),
			],
		],

		'positions' => [
			'unspecified' => 'Unspecified',
			'handler' => 'Handler',
			'cutter' => 'Cutter',
			'striker' => 'Striker',
			'olinehandler' => 'O Line Handler',
			'olinecutter' => 'O Line Cutter',
			'olinestriker' => 'O Line Striker',
			'dlinehandler' => 'D Line Handler',
			'dlinecutter' => 'D Line Cutter',
			'dlinestriker' => 'D Line Striker',
		],

		'score_options' => [
			'Goal' => 1,
		],

		'other_options' => [
			'Half' => 'Pull to start second half',
			'Injury' => 'Injury substitution',
		],

		'rating_questions' => [
			__('Skill') => [
				__('Compared to other players of the same sex as you, would you consider yourself:') => [
					0 => __('One of the slowest'),
					1 => __('Slower than most'),
					2 => __('Average speed'),
					3 => __('Faster than most'),
					4 => __('One of the fastest'),
				],

				__('How would you describe your throwing skills?') => [
					0 => __('just learning, only backhand throw, no forehand'),
					1 => __('can make basic throws, perhaps weaker forehand, some distance and accuracy, nervous when handling the disc'),
					2 => __('basic throws (backhand and forehand), some distance and accuracy, not very consistent, somewhat intimidated when handling the disc, can handle on most lower-tier teams'),
					3 => __('good basic throws (backhand and forehand), good distance and accuracy, fairly consistent, relatively comfortable when handling disc, can handle on most lower to mid-tier teams'),
					4 => __('very good basic throws, know some other kinds of throws, very good distance and accuracy, usually consistent quality throws, confident when handling the disc, can handle on most mid to upper-tier teams'),
					5 => __('all kinds of throws, excellent distance and accuracy, not prone to errors of judgment, can handle on most top-tier and lower-competitive teams'),
					6 => __('all kinds of throws, very rarely make a bad throw, excellent distance, near perfect accuracy, epitome of reliability, can handle on an elite team (mid-highly competitive team)'),
				],

				__('How would you rate your catching skills?') => [
					0 => __('can make basic catches if they\'re straight to me, still learning to judge the flight path of the disc'),
					1 => __('can make basic catches, sometimes have difficulty judging the flight path of the disc'),
					2 => __('can make most catches, good at judging the flight path of the disc, not likely to attempt a layout'),
					3 => __('can catch almost everything (high, low, to the side), rarely misread the disc, will layout if necessary'),
					4 => __('catch absolutely everything thrown towards me, and most of the swill that isn\'t'),
				],

				__('With respect to playing defense, you:') => [
					0 => __('understand some basics, and are learning how to read the play, no/limited experience with defense strategies'),
					1 => __('know the basics, but you\'re sometimes behind the play, learned a bit about match defense strategies'),
					2 => __('can stay with the play and sometimes make the D, understand the basics of match & zone style defense strategies'),
					3 => __('can read and anticipate the play and get in position to increase the chances of make the D, comfortable with both match/zone style defense strategies'),
					4 => __('always think ahead of the play and can often make the D, proficient at both match/zone style defense strategies and maybe know a few more'),
				],

				__('With respect to playing offense, you:') => [
					0 => __('are still learning the basic strategy, not quite sure where to go or when to cut'),
					1 => __('have the basic idea of where/when/how cuts should be made, starting to be able to do it, basic knowledge of a stack'),
					2 => __('can make decent cuts, understand the stack, can play at least one of handler/striker/popper/etc, understand the concept of the dump & swing'),
					3 => __('can make good cuts, can play any of handler/striker/popper/etc, comfortable handling, rarely throw away the disc or get blocked'),
					4 => __('proficient cutter, experienced handler, can play any position, understand many offensive strategies'),
				],
			],

			__('Experience') => [

				__('For how many years have you been playing Ultimate?') => [
					0 => __('0 years'),
					1 => __('1-2 years'),
					2 => __('3-5 years'),
					3 => __('6-8 years'),
					4 => __('9+ years'),
				],

				__('What is the highest level at which you regularly play?') => [
					0 => __('Recreational League'),
					1 => __('Intermediate League or Recreational Tournament'),
					2 => __('Competitive League or Intermediate Tournament'),
					3 => __('Competitive Tournament (top 8 at a high-caliber tournament or bottom half at Nationals'),
					4 => __('Elite Tournament (top half at Nationals)'),
				],

				__('Over the past few summers, how many nights during the week did you play Ultimate? (Organized practices and regular pick-up count.)') => [
					0 => __('0 nights per week'),
					1 => __('1 night per week'),
					2 => __('2 nights per week'),
					3 => __('3 nights per week'),
					4 => __('more than 3 nights per week'),
				],

				__('Over the past few years, when did you normally play Ultimate?') => [
					0 => __('The occasional pick-up game'),
					1 => __('The occasional tournament'),
					2 => __('1 season (e.g. Summer, Fall or Winter)'),
					3 => __('2 seasons'),
					4 => __('Year-round'),
				],

				__('If there was a disagreement on the field about a certain play, the majority of the time you would be able to:') => [
					0 => __('not do much because you don\'t know all the rules yet'),
					1 => __('quote what you think is the rule, and agree with the other player/captain to go with that'),
					// 2 intentionally omitted to give this question equal weight to the others
					3 => __('use a copy of the rules to find the exact rule that addresses the problem'),
					4 => __('quote the exact rule from memory that addresses the problem'),
				],
			],
		],
	],

	'volleyball' => [
		'field' => $court,
		'field_cap' => Inflector::humanize($court),
		'fields' => Inflector::pluralize($court),
		'fields_cap' => Inflector::humanize(Inflector::pluralize($court)),

		'start' => [
			'stat_sheet' => 'Initial serve',
			'stat_sheet_direction' => true,
			'live_score' => 'Serving team',
			'box_score' => '{0} served',
			'twitter' => '{1} serves to {0}',
		],

		'roster_requirements' => [
			'3/3' => 10,
			'4/2' => 10,
			'3/2' => 8,
			'2/2' => 7,
			'womens' => 10,
			'mens' => 10,
			'open' => 10,
		],

		'positions' => [
			'unspecified' => 'Unspecified',
			'hitter' => 'Hitter',
			'attacker' => 'Attacker',
			'setter' => 'Setter',
			'blocker' => 'Blocker',
			'middleblocker' => 'Middle Blocker',
			'outsidehitter' => 'Outside Hitter',
			'weaksidehitter' => 'Weakside Hitter',
			'liberos' => 'Liberos',
		],

		'score_options' => [
			'Point' => 1,
		],

		'other_options' => [
			// TODO
		],

		'rating_questions' => false,
	],
];

foreach (array_keys($config['sports']) as $sport) {
	if (file_exists(ZULURU_CONFIG . 'sport/' . $sport . '_custom.php')) {
		include(ZULURU_CONFIG . 'sport/' . $sport . '_custom.php');
	}

	$config['sports'][$sport]['ratio_rule'] = make_human_options(array_keys($config['sports'][$sport]['roster_requirements']));
}

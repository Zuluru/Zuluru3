<?php
use Migrations\AbstractSeed;

/**
 * Waivers seed.
 */
class WaiversSeed extends AbstractSeed {
	/**
	 * Data Method.
	 *
	 * @return mixed
	 */
	public function data() {
		return [
			[
				'name' => __d('seeds', 'Membership'),
				'description' => __d('seeds', 'Membership'),
				'text' => '<p>You must agree to the following waiver in order to play in any %short_name% games.</p>
<p>While the sport of Ultimate is understood to be a non-contact sport, this does not mean that contact does not occur. Ultimate is a fast paced, athletic sport, and as such has all of the attendant risks that are commonly associated with such an activity. While all players are expected to adhere to the Spirit of the Game as outlined in the rules of the sport, it is not possible to absolutely ensure that contact between players will not occur, and that injuries will not be sustained. Members are reminded that they engage in the sport entirely at their own risk.</p>
<p>Given that %short_name% offers the sport of Ultimate at varied and dispersed locations, and does not have officials such as referees at the games, it falls to the Captains and players of the teams participating in the game to be responsible for the safety of the site at which they are playing. Captains and players are jointly responsible to examine the %field% before their game begins, to determine if it is safe. They are responsible for taking appropriate action if they feel that their safety is being compromised by playing on what they consider an unsafe %field%; this includes but is not limited to the refusal, as a team or individually, to play on a site they deem to be potentially hazardous. %short_name% never forces its members to play when they are not comfortable doing so; games can easily be rescheduled.</p>
<p>While participating in %short_name% sponsored games of Ultimate, all players have the duty to take reasonable efforts to ensure their own safety, the safety of their teammates, and the safety of the opposing players. This includes but is not limited to the refusal, as a team or individually, to play against an opponent they feel is playing in an unsafe manner, and forcing a teammate to stop playing if s/he is alleged to be playing in an unsafe manner by a teammate or by a member of the opposing team acting in good faith. Captains have the additional duty to discuss any safety issues with the Captain from the opposing team, where (a) a teammate requests that such a discussion be held; and/or (b) when the Captain deems that it is necessary to do so.</p>
<p>I, as a member of and player participating in %short_name%\'s Ultimate League, acknowledge that there is a risk of being injured while playing the game of Ultimate and that %short_name% does not warrant or guarantee the quality or condition of Ultimate %fields% and other facilities, and, accordingly, I agree that I will not conduct myself in a dangerous or reckless manner while participating in the game of Ultimate, will comply with the rules of the game of Ultimate and will, and do hereby, accept full responsibility for (a) the risk of any personal injury or harm experienced by me if not caused by another player in a reckless or dangerous manner; and (b) any personal injury or harm caused by me to others if done so in a reckless or dangerous manner while playing or participating in the game of Ultimate.</p>
<p>I acknowledge that %short_name% occasionally issues directions, orders and regulations for the use of %fields% and other facilities, for the rules of play for the game of Ultimate and for the conduct of members and others while on or about the Ultimate %fields% and that such directions, orders and regulations are reasonable, and, accordingly, I hereby agree to comply with all such directions, orders and regulations issued by %short_name%.</p>
<p>I, in consideration of my membership in %short_name% and my participation in Ultimate games, do hereby release and forever discharge %short_name%, its directors, officers, employees, volunteers and other representatives from any and all damages, costs and claims for any present or future injury, however grievous or serious it may be, which I may suffer or which any person under my care may suffer as a result of or arising from my participation in the game of Ultimate and my use of any facility of %short_name% and, further, I do hereby agree to indemnify and save %short_name%, its directors, officers and employees harmless from and against any and all such damages, costs and claims whatsoever and from and against any and all damages, costs and claims whatsoever which may arise from or as a result of any injury, however grievous or serious it may be, which I may negligently or wilfully cause to others during my participation in the game of Ultimate and my use of any facility of %short_name%.</p>
<p>I agree to play fairly and by the rules, having regard to the safety and the enjoyment of other members.</p>',
				'expiry_type' => 'fixed_dates',
				'start_month' => 1,
				'start_day' => 1,
				'end_month' => 12,
				'end_day' => 31,
			],
			[
				'name' => __d('seeds', 'Event'),
				'description' => __d('seeds', 'Event'),
				'text' => '<p>You must agree to the following waiver in order to participate in any %short_name% events.</p>
<p>While the sport of Ultimate is understood to be a non-contact sport, this does not mean that contact does not occur. Ultimate is a fast paced, athletic sport, and as such has all of the attendant risks that are commonly associated with such an activity. While all players are expected to adhere to the Spirit of the Game as outlined in the rules of the sport, it is not possible to absolutely ensure that contact between players will not occur, and that injuries will not be sustained. Participants are reminded that they engage in the sport entirely at their own risk.</p>
<p>Given that %short_name% offers the sport of Ultimate at varied and dispersed locations, and does not have officials such as referees at the games, it falls to the Captains and players of the teams participating in the game to be responsible for the safety of the site at which they are playing. Captains and players are jointly responsible to examine the %field% before their game begins, to determine if it is safe. They are responsible for taking appropriate action if they feel that their safety is being compromised by playing on what they consider an unsafe %field%; this includes but is not limited to the refusal, as a team or individually, to play on a site they deem to be potentially hazardous. %short_name% never forces participants to play when they are not comfortable doing so; games can easily be rescheduled.</p>
<p>While participating in %short_name% sponsored Ultimate events, all players have the duty to take reasonable efforts to ensure their own safety, the safety of their teammates, and the safety of the opposing players. This includes but is not limited to the refusal, as a team or individually, to play against an opponent they feel is playing in an unsafe manner, and forcing a teammate to stop playing if s/he is alleged to be playing in an unsafe manner by a teammate or by a member of the opposing team acting in good faith. Captains have the additional duty to discuss any safety issues with the Captain from the opposing team, where (a) a teammate requests that such a discussion be held; and/or (b) when the Captain deems that it is necessary to do so.</p>
<p>I, as a player participating in %short_name%\'s Ultimate event, acknowledge that there is a risk of being injured while playing the game of Ultimate and that %short_name% does not warrant or guarantee the quality or condition of Ultimate %fields% and other facilities, and, accordingly, I agree that I will not conduct myself in a dangerous or reckless manner while participating in the game of Ultimate, will comply with the rules of the game of Ultimate and will, and do hereby, accept full responsibility for (a) the risk of any personal injury or harm experienced by me if not caused by another player in a reckless or dangerous manner; and (b) any personal injury or harm caused by me to others if done so in a reckless or dangerous manner while playing or participating in the game of Ultimate.</p>
<p>I acknowledge that %short_name% occasionally issues directions, orders and regulations for the use of %fields% and other facilities, for the rules of play for the game of Ultimate and for the conduct of members and others while on or about the Ultimate %fields% and that such directions, orders and regulations are reasonable, and, accordingly, I hereby agree to comply with all such directions, orders and regulations issued by %short_name%.</p>
<p>I, in consideration of my participation in %short_name%\'s Ultimate events, do hereby release and forever discharge %short_name%, its directors, officers, employees, volunteers and other representatives from any and all damages, costs and claims for any present or future injury, however grievous or serious it may be, which I may suffer or which any person under my care may suffer as a result of or arising from my participation in the game of Ultimate and my use of any facility of %short_name% and, further, I do hereby agree to indemnify and save %short_name%, its directors, officers and employees harmless from and against any and all such damages, costs and claims whatsoever and from and against any and all damages, costs and claims whatsoever which may arise from or as a result of any injury, however grievous or serious it may be, which I may negligently or wilfully cause to others during my participation in the game of Ultimate and my use of any facility of %short_name%.</p>
<p>I agree to play fairly and by the rules, having regard to the safety and the enjoyment of other participants.</p>',
				'expiry_type' => 'event',
			],
			[
				'name' => __d('seeds', 'Dog'),
				'description' => __d('seeds', 'Dog'),
				'text' => '<p>Since you have indicated you wish to bring a dog to the %fields%, you must agree to the following waiver.</p>
<p>I am a member of the %name% (%short_name%), and the owner of a dog I wish to bring to %short_name% games, and have read and understood this consent, and as such do hereby agree to be responsible for the conduct and actions of said dog and do hereby release and discharge %short_name% from any and all responsibilities, liabilities and complaints arising from the conduct and actions of my said dog in or about any properties used by %short_name%.</p>
<p>In respect of said dog, I agree to comply with the following rules:
<ol>
	<li>that I will be in control of my dog at all times, and will ensure that my dog is leashed and restrained while games are in progress.</li>
	<li>that I will pick up ("poop &amp; scoop") any and all fecal matter left by my dog at any of the said properties, and remove said waste and any bags containing said waste from the properties when I leave.</li>
	<li>that I will not under any circumstances put plastic bags in the porta potties or in the bushes, long grass, or elsewhere on or about the said properties.</li>
</ol>
</p>
<p>I agree that I will pay any and all damages, costs and claims arising from the conduct and actions of my said dog and will indemnify and save %short_name%, its directors, officers, and employees harmless from any such damages, costs and claims.</p>',
				'expiry_type' => 'fixed_dates',
				'start_month' => 1,
				'start_day' => 1,
				'end_month' => 12,
				'end_day' => 31,
			],
		];
	}

	/**
	 * Run Method.
	 *
	 * @return void
	 */
	public function run() {
		$table = $this->table('waivers');
		$table->insert($this->data())->save();
	}
}

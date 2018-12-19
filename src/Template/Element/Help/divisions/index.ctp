<?php
use Cake\Core\Configure;

$identity = $this->Authorize->getIdentity();
if ($identity && ($identity->isManager() || $identity->isCoordinator())) {
	echo $this->element('Help/topics', [
			'section' => 'divisions',
			'topics' => [
				'edit' => [
					'image' => 'edit_32.png',
				],
				'add_teams' => [
					'image' => 'team_add_32.png',
				],
				'roster_add' => [
					'image' => 'roster_add_32.png',
				],
				'emails' => [
					'image' => 'email_32.png',
				],
				'fields' => [
					'image' => 'field_report_32.png',
					'title' => __('{0} Distribution Report', __(Configure::read('UI.field_cap'))),
				],
				'slots' => __('{0} Availability Report', __(Configure::read('UI.field_cap'))),
				'spirit' => [
					'image' => 'spirit_32.png',
					'title' => 'Spirit Report',
				],
				'approve_scores' => [
					'image' => 'score_approve_32.png',
				],
			],
	]);
}

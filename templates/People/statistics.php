<?php
/**
 * @var \App\View\AppView $this
 * @var int[] $affiliates
 * @var \Cake\ORM\Entity[] $status_count
 * @var \Cake\ORM\Entity[] $group_count
 * @var \Cake\ORM\Entity[] $gender_count
 * @var \Cake\ORM\Entity[] $age_count
 * @var \Cake\ORM\Entity[] $started_count
 * @var \Cake\ORM\Entity[] $skill_count
 * @var \Cake\ORM\Entity[] $city_count
 */

use Cake\Core\Configure;
use Cake\Utility\Inflector;

$this->Breadcrumbs->add(__('People'));
$this->Breadcrumbs->add(__('Statistics'));

$multi_sport = (count(Configure::read('options.sport')) > 1);
?>

<div class="people statistics">
	<h2><?= __('People Statistics') ?></h2>

	<h3><?= __('People by Account Status') ?></h3>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Status') ?></th>
					<th><?= __('People') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
$total = 0;
$affiliate_id = null;
foreach ($status_count as $status):
	if (count($affiliates) > 1 && $status->_matchingData['Affiliates']->id != $affiliate_id):
		$affiliate_id = $status->_matchingData['Affiliates']->id;
		if ($total):
?>
				<tr>
					<td><?= __('Total') ?></td>
					<td><?= $total ?></td>
				</tr>
<?php
		endif;

		$total = 0;
?>
				<tr>
					<th colspan="2">
						<h4 class="affiliate"><?= $status->_matchingData['Affiliates']->name ?></h4>
					</th>
				</tr>
<?php
	endif;

	$total += $status->person_count;
?>
				<tr>
					<td><?= $status->status ?></td>
					<td><?= $status->person_count ?></td>
				</tr>
<?php
endforeach;
?>

				<tr>
					<td><?= __('Total') ?></td>
					<td><?= $total ?></td>
				</tr>
			</tbody>
		</table>
	</div>

	<h3><?= __('People by Account Class') ?></h3>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Class') ?></th>
					<th><?= __('Players') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
$affiliate_id = null;
foreach ($group_count as $group):
	if (count($affiliates) > 1 && $group->_matchingData['Affiliates']->id != $affiliate_id):
		$affiliate_id = $group->_matchingData['Affiliates']->id;
?>
				<tr>
					<th colspan="2">
						<h4 class="affiliate"><?= $group->_matchingData['Affiliates']->name ?></h4>
					</th>
				</tr>
<?php
	endif;
?>
				<tr>
					<td><?php
						if (empty($group->_matchingData['Groups']->name)) {
							echo __('None');
						} else {
							echo $group->_matchingData['Groups']->name;
						}
					?></td>
					<td><?= $group->person_count ?></td>
				</tr>
<?php
endforeach;
?>
			</tbody>
		</table>
	</div>

	<h3><?= __('Players by Gender') ?></h3>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
<?php
if ($multi_sport):
?>
					<th><?= __('Sport') ?></th>
<?php
endif;
?>
					<th><?= __('Gender') ?></th>
					<th><?= __('Players') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
$total = 0;
$affiliate_id = $sport = null;
foreach ($gender_count as $gender):
	if (count($affiliates) > 1 && $gender->_matchingData['Affiliates']->id != $affiliate_id):
		$affiliate_id = $gender->_matchingData['Affiliates']->id;
		$sport = null;
		if ($total):
?>
				<tr>
<?php
			if ($multi_sport):
?>
					<td></td>
<?php
			endif;
?>
					<td><?= __('Total') ?></td>
					<td><?= $total ?></td>
				</tr>
<?php
		endif;

		$total = 0;
?>
				<tr>
					<th colspan="<?= 2 + $multi_sport ?>">
						<h4 class="affiliate"><?= $gender->_matchingData['Affiliates']->name ?></h4>
					</th>
				</tr>
<?php
	endif;

	if ($multi_sport && $gender->_matchingData['Skills']->sport != $sport):
		$sport = $gender->_matchingData['Skills']->sport;
		if ($total):
?>
				<tr>
					<td></td>
					<td><?= __('Total') ?></td>
					<td><?= $total ?></td>
				</tr>
<?php
		endif;

		$total = 0;
		$sport_title = Inflector::humanize($sport);
	endif;

	$total += $gender->person_count;
?>
				<tr>
<?php
	if ($multi_sport):
?>
					<td><?= $sport_title; $sport_title = '' ?></td>
<?php
	endif;
?>
					<td><?= $gender->gender ?></td>
					<td><?= $gender->person_count ?></td>
				</tr>
<?php
endforeach;
?>

				<tr>
<?php
if ($multi_sport):
?>
					<td></td>
<?php
endif;
?>
					<td><?= __('Total') ?></td>
					<td><?= $total ?></td>
				</tr>
			</tbody>
		</table>
	</div>

<?php
if (isset($roster_designation_count)):
?>
	<h3><?= __('Players by Roster Designation') ?></h3>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
<?php
if ($multi_sport):
?>
					<th><?= __('Sport') ?></th>
<?php
endif;
?>
					<th><?= __('Gender') ?></th>
					<th><?= __('Players') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
$total = 0;
$affiliate_id = $sport = null;
foreach ($roster_designation_count as $roster_designation):
	if (count($affiliates) > 1 && $roster_designation->_matchingData['Affiliates']->id != $affiliate_id):
		$affiliate_id = $roster_designation->_matchingData['Affiliates']->id;
		$sport = null;
		if ($total):
?>
				<tr>
<?php
			if ($multi_sport):
?>
					<td></td>
<?php
			endif;
?>
					<td><?= __('Total') ?></td>
					<td><?= $total ?></td>
				</tr>
<?php
		endif;

		$total = 0;
?>
				<tr>
					<th colspan="<?= 2 + $multi_sport ?>">
						<h4 class="affiliate"><?= $roster_designation->_matchingData['Affiliates']->name ?></h4>
					</th>
				</tr>
<?php
	endif;

	if ($multi_sport && $roster_designation->_matchingData['Skills']->sport != $sport):
		$sport = $roster_designation->_matchingData['Skills']->sport;
		if ($total):
?>
				<tr>
					<td></td>
					<td><?= __('Total') ?></td>
					<td><?= $total ?></td>
				</tr>
<?php
		endif;

		$total = 0;
		$sport_title = Inflector::humanize($sport);
	endif;

	$total += $roster_designation->person_count;
?>
				<tr>
<?php
	if ($multi_sport):
?>
					<td><?= $sport_title; $sport_title = '' ?></td>
<?php
	endif;
?>
					<td><?= $roster_designation->roster_designation?></td>
					<td><?= $roster_designation->person_count ?></td>
				</tr>
<?php
endforeach;
?>

				<tr>
<?php
if ($multi_sport):
?>
					<td></td>
<?php
endif;
?>
					<td><?= __('Total') ?></td>
					<td><?= $total ?></td>
				</tr>
			</tbody>
		</table>
	</div>

<?php
endif;

if (Configure::read('profile.birthdate')):
?>
	<h3><?= __('Players by Age') ?></h3>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
<?php
	if ($multi_sport):
?>
					<th><?= __('Sport') ?></th>
<?php
	endif;
?>
					<th><?= __('Age') ?></th>
					<th><?= __('Players') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	$total = 0;
	$affiliate_id = $sport = null;
	foreach ($age_count as $age):
		if (count($affiliates) > 1 && $age->_matchingData['Affiliates']->id != $affiliate_id):
			$affiliate_id = $age->_matchingData['Affiliates']->id;
			$sport = null;
			if ($total):
?>
				<tr>
<?php
				if ($multi_sport):
?>
					<td></td>
<?php
				endif;
?>
					<td><?= __('Total') ?></td>
					<td><?= $total ?></td>
				</tr>
<?php
			endif;

			$total = 0;
?>
				<tr>
					<th colspan="<?= 2 + $multi_sport ?>">
						<h4 class="affiliate"><?= $age->_matchingData['Affiliates']->name ?></h4>
					</th>
				</tr>
<?php
		endif;

		if ($multi_sport && $age->_matchingData['Skills']->sport != $sport):
			$sport = $age->_matchingData['Skills']->sport;
			if ($total):
?>
				<tr>
					<td></td>
					<td><?= __('Total') ?></td>
					<td><?= $total ?></td>
				</tr>
<?php
			endif;

			$total = 0;
			$sport_title = Inflector::humanize($sport);
		endif;

		$total += $age->person_count;
?>
				<tr>
<?php
		if ($multi_sport):
?>
					<td><?= $sport_title; $sport_title = '' ?></td>
<?php
		endif;
?>
					<td><?= $age->age_bucket . ' to ' . ($age->age_bucket + 4) ?></td>
					<td><?= $age->person_count ?></td>
				</tr>
<?php
	endforeach;
?>

				<tr>
<?php
	if ($multi_sport):
?>
					<td></td>
<?php
	endif;
?>
					<td><?= __('Total') ?></td>
					<td><?= $total ?></td>
				</tr>
			</tbody>
		</table>
	</div>
<?php
endif;

if (Configure::read('profile.year_started')):
?>
	<h3><?= __('Players by Year Started') ?></h3>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
<?php
	if ($multi_sport):
?>
					<th><?= __('Sport') ?></th>
<?php
	endif;
?>
					<th><?= __('Year') ?></th>
					<th><?= __('Players') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	$total = 0;
	$affiliate_id = $sport = null;
	foreach ($started_count as $started):
		if (count($affiliates) > 1 && $started->_matchingData['Affiliates']->id != $affiliate_id):
			$affiliate_id = $started->_matchingData['Affiliates']->id;
			$sport = null;
			if ($total):
?>
				<tr>
<?php
				if ($multi_sport):
?>
					<td></td>
<?php
				endif;
?>
					<td><?= __('Total') ?></td>
					<td><?= $total ?></td>
				</tr>
<?php
			endif;

			$total = 0;
?>
				<tr>
					<th colspan="<?= 2 + $multi_sport ?>">
						<h4 class="affiliate"><?= $started->_matchingData['Affiliates']->name ?></h4>
					</th>
				</tr>
<?php
		endif;

		if ($multi_sport && $started->_matchingData['Skills']->sport != $sport):
			$sport = $started->_matchingData['Skills']->sport;
			if ($total):
?>
				<tr>
					<td></td>
					<td><?= __('Total') ?></td>
					<td><?= $total ?></td>
				</tr>
<?php
			endif;

			$total = 0;
			$sport_title = Inflector::humanize($sport);
		endif;

		$total += $started->person_count;
?>
				<tr>
<?php
		if ($multi_sport):
?>
					<td><?= $sport_title; $sport_title = '' ?></td>
<?php
		endif;
?>
					<td><?= $started->_matchingData['Skills']->year_started ?></td>
					<td><?= $started->person_count ?></td>
				</tr>
<?php
	endforeach;
?>

				<tr>
<?php
	if ($multi_sport):
?>
					<td></td>
<?php
	endif;
?>
					<td><?= __('Total') ?></td>
					<td><?= $total ?></td>
				</tr>
			</tbody>
		</table>
	</div>
<?php
endif;

if (Configure::read('profile.skill_level')):
?>
	<h3><?= __('Players by Skill Level') ?></h3>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
<?php
	if ($multi_sport):
?>
					<th><?= __('Sport') ?></th>
<?php
	endif;
?>
					<th><?= __('Skill Level') ?></th>
					<th><?= __('Players') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	$total = 0;
	$affiliate_id = $sport = null;
	foreach ($skill_count as $skill):
		if (count($affiliates) > 1 && $skill->_matchingData['Affiliates']->id != $affiliate_id):
			$affiliate_id = $skill->_matchingData['Affiliates']->id;
			$sport = null;
			if ($total):
?>
				<tr>
<?php
				if ($multi_sport):
?>
					<td></td>
<?php
				endif;
?>
					<td><?= __('Total') ?></td>
					<td><?= $total ?></td>
				</tr>
<?php
			endif;

			$total = 0;
?>
				<tr>
					<th colspan="<?= 2 + $multi_sport ?>">
						<h4 class="affiliate"><?= $skill->_matchingData['Affiliates']->name ?></h4>
					</th>
				</tr>
<?php
		endif;

		if ($multi_sport && $skill->_matchingData['Skills']->sport != $sport):
			$sport = $skill->_matchingData['Skills']->sport;
			if ($total):
?>
				<tr>
					<td></td>
					<td><?= __('Total') ?></td>
					<td><?= $total ?></td>
				</tr>
<?php
			endif;

			$total = 0;
			$sport_title = Inflector::humanize($sport);
		endif;

		$total += $skill->person_count;
?>
				<tr>
<?php
		if ($multi_sport):
?>
					<td><?= $sport_title; $sport_title = '' ?></td>
<?php
		endif;
?>
					<td><?= $skill->_matchingData['Skills']->skill_level ?></td>
					<td><?= $skill->person_count ?></td>
				</tr>
<?php
	endforeach;
?>

				<tr>
<?php
	if ($multi_sport):
?>
					<td></td>
<?php
	endif;
?>
					<td><?= __('Total') ?></td>
					<td><?= $total ?></td>
				</tr>
			</tbody>
		</table>
	</div>
<?php
endif;

if (Configure::read('profile.addr_city')):
?>
	<h3><?= __('Players by City') ?></h3>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
<?php
	if ($multi_sport):
?>
					<th><?= __('Sport') ?></th>
<?php
	endif;
?>
					<th><?= __('City') ?></th>
					<th><?= __('Players') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	$total = 0;
	$affiliate_id = $sport = null;
	foreach ($city_count as $city):
		if (count($affiliates) > 1 && $city->_matchingData['Affiliates']->id != $affiliate_id):
			$affiliate_id = $city->_matchingData['Affiliates']->id;
			$sport = null;
			if ($total):
?>
				<tr>
<?php
				if ($multi_sport):
?>
					<td></td>
<?php
				endif;
?>
					<td><?= __('Total') ?></td>
					<td><?= $total ?></td>
				</tr>
<?php
			endif;

			$total = 0;
?>
				<tr>
					<th colspan="<?= 2 + $multi_sport ?>">
						<h4 class="affiliate"><?= $city->_matchingData['Affiliates']->name ?></h4>
					</th>
				</tr>
<?php
		endif;

		if ($multi_sport && $city->_matchingData['Skills']->sport != $sport):
			$sport = $city->_matchingData['Skills']->sport;
			if ($total):
?>
				<tr>
					<td></td>
					<td><?= __('Total') ?></td>
					<td><?= $total ?></td>
				</tr>
<?php
			endif;

			$total = 0;
			$sport_title = Inflector::humanize($sport);
		endif;

		$total += $city->person_count;
?>
				<tr>
<?php
		if ($multi_sport):
?>
					<td><?= $sport_title; $sport_title = '' ?></td>
<?php
		endif;
?>
					<td><?php
						if (empty($city->addr_city)) {
							echo __('Unspecified');
						} else {
							echo $city->addr_city;
						}
					?></td>
					<td><?= $city->person_count ?></td>
				</tr>
<?php
	endforeach;
?>

				<tr>
<?php
	if ($multi_sport):
?>
					<td></td>
<?php
	endif;
?>
					<td><?= __('Total') ?></td>
					<td><?= $total ?></td>
				</tr>
			</tbody>
		</table>
	</div>
<?php
endif;
?>

</div>

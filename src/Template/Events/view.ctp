<?php
/**
 * @var \App\Model\Entity\Event $event
 * @var \App\Model\Entity\GameSlot[] $times
 * @var \App\Model\Entity\Affiliate[] $affiliates
 * @var string[] $facilities
 * @var bool $allowed
 * @var string[] $notices
 */

use App\Model\Table\PricesTable;
use Cake\Utility\Inflector;

$this->Breadcrumbs->add(__('Event'));
$this->Breadcrumbs->add(h($event->name));
$this->Breadcrumbs->add(__('View'));
?>

<?php
$deposit = collection($event->prices)->some(function ($price) { return $price->allow_deposit; });
$admin_register = false;
$identity = $this->Authorize->getIdentity();
?>

<div class="events view">
	<h2><?php
	echo h($event->name);
	if (count($affiliates) > 1) {
		echo __(' ({0})', $this->Html->link($event->affiliate->name, ['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => $event->affiliate->id]));
	}
	?></h2>
	<?= $this->element('Registrations/relative_notice') ?>
	<?= $event->description ?>
	<dl class="dl-horizontal">
		<dt><?= __('Event Type') ?></dt>
		<dd><?= __($event->event_type->name) ?></dd>
<?php
if ($event->has('level_of_play') && !empty($event->level_of_play)):
?>
		<dt><?= __('Level of Play') ?></dt>
		<dd><?= $event->level_of_play ?></dd>
<?php
endif;

if (!empty($event->division_id)):
	if (!empty($facilities) && count($facilities) < 6):
		$facility_links = [];
		foreach ($facilities as $facility_id => $facility_name) {
			$facility_links[] = $this->Html->link($facility_name, ['controller' => 'Facilities', 'action' => 'view', 'facility' => $facility_id]);
		}
?>
		<dt><?= __n('Location', 'Locations', count($facilities)) ?></dt>
		<dd><?= implode(', ', $facility_links) ?></dd>
<?php
	endif;
?>
		<dt><?= __('First Game') ?></dt>
		<dd><?= $this->Time->date($event->division->open) ?></dd>
		<dt><?= __('Last Game') ?></dt>
		<dd><?= $this->Time->date($event->division->close) ?></dd>
<?php
	if (!empty($event->division->days)):
?>
		<dt><?= __n('Day', 'Days', count($event->division->days)) ?></dt>
		<dd><?php
			$days = [];
			foreach ($event->division->days as $day) {
				$days[] = __($day->name);
			}
			echo implode(', ', $days);
		?></dd>
<?php
	endif;

	if (!empty($times) && count($times) < 5):
		$time_list = [];
		foreach ($times as $slot) {
			if ($slot->game_end) {
				$time_list[] = $this->Time->time($slot->game_start) . '-' . $this->Time->time($slot->display_game_end);
			} else {
				$time_list[] = $this->Time->time($slot->game_start);
			}
		}
?>
		<dt><?= __n('Game Time', 'Game Times', count($times)) ?></dt>
		<dd><?= implode(', ', $time_list) ?></dd>
<?php
	endif;
?>
		<dt><?= __('Ratio Rule') ?></dt>
		<dd><?= __(Inflector::Humanize($event->division->ratio_rule)) ?></dd>
<?php
endif;

if (!empty($event->membership_begins)):
?>
		<dt><?= __('Membership Begins') ?></dt>
		<dd><?= $this->Time->date($event->membership_begins) ?></dd>
		<dt><?= __('Membership Ends') ?></dt>
		<dd><?= $this->Time->date($event->membership_ends) ?></dd>
<?php
endif;

if ($event->women_cap == CAP_COMBINED && $event->open_cap > 0):
?>
		<dt><?= __('Registration Cap') ?></dt>
		<dd><?= $event->open_cap ?></dd>
<?php
else:
	if ($event->open_cap > 0):
?>
		<dt><?= __('Open Cap') ?></dt>
		<dd><?= $event->open_cap ?></dd>
<?php
	endif;
	if ($event->women_cap > 0):
?>
		<dt><?= __('Women Cap') ?></dt>
		<dd><?= $event->women_cap ?></dd>
<?php
	endif;
endif;
?>
		<dt><?= __('Multiples') ?></dt>
		<dd><?= $event->multiple ? __('Allowed') : __('Not allowed') ?></dd>

<?php
if (count($event->prices) == 1):
	if ($event->prices[0]->canRegister && $event->prices[0]->canRegister['allowed'] && $event->prices[0]->open->isFuture() && $identity->isManagerOf($event)) {
		$admin_register = true;
	}
?>
		<dt><?= __('Registration Opens') ?></dt>
		<dd><?= $this->Time->datetime($event->prices[0]->open) ?></dd>
		<dt><?= __('Registration Closes') ?></dt>
		<dd><?= $this->Time->datetime($event->prices[0]->close) ?></dd>
		<dt><?= __('Reservations') ?></dt>
		<dd><?= ($event->prices[0]->allow_reservations ? PricesTable::duration($event->prices[0]->reservation_duration) : __('No')) ?></dd>
		<dt><?= __('Cost') ?></dt>
		<dd><?php
			$cost = $event->prices[0]->total;
			if ($cost > 0) {
				echo $this->Number->currency($cost);
			} else {
				echo $this->Html->tag('span', __('FREE'), ['class' => 'free']);
			}
		?></dd>
<?php
	if ($deposit):
?>
		<dt><?= __('Deposit') ?></dt>
		<dd><?php
			echo $this->Number->currency($event->prices[0]->minimum_deposit);
			if (!$event->prices[0]->fixed_deposit) {
				echo '+';
			}
		?></dd>
<?php
	endif;
endif;
?>
	</dl>

<?php
if (count($event->prices) > 1):
?>
<div class="related row">
	<div class="column">
		<h4 class="subheader"><?= __('Registration Options') ?></h4>
		<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Option') ?></th>
					<th><?= __('Registration Opens') ?></th>
					<th><?= __('Registration Closes') ?></th>
					<th><?= __('Reservations?') ?></th>
					<th><?= __('Cost') ?></th>
<?php
	 if ($deposit):
?>
					<th><?= __('Deposit') ?></th>
<?php
	 endif;
?>
					<th><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	foreach ($event->prices as $price):
?>
				<tr>
					<td><?= $price->name ?></td>
					<td><?= $this->Time->datetime($price->open) ?></td>
					<td><?= $this->Time->datetime($price->close) ?></td>
					<td><?= ($price->allow_reservations ? PricesTable::duration($price->reservation_duration) : __('No')) ?></td>
					<td><?php
						$cost = $price->total;
						if ($cost > 0) {
							echo $this->Number->currency($cost);
						} else {
							echo $this->Html->tag('span', __('FREE'), ['class' => 'free']);
						}
					?></td>
<?php
		if ($deposit):
?>
					<td><?php
						if ($price->allow_deposit) {
							echo $this->Number->currency($price->minimum_deposit);
							if (!$price->fixed_deposit) {
								echo '+';
							}
						} else {
							echo __('N/A');
						}
					?></td>
<?php
		endif;
?>
					<td class="actions"><?php
						if (!empty($price->canRegister['allowed'])) {
							echo $this->Html->link(__('Register now!'),
								['controller' => 'Registrations', 'action' => 'register', 'event' => $id, 'variant' => $price->id],
								['title' => __('Register for {0}', $event->name . ' ' . $price->name)]
							);
							if ($price->open->isFuture() && $identity->isManagerOf($event)) {
								$admin_register = true;
							}
						}
						if ($this->Authorize->can('delete', $price)) {
							echo $this->Form->iconPostLink('delete_24.png',
								['controller' => 'Prices', 'action' => 'delete', 'price' => $price->id],
								['alt' => __('Delete'), 'title' => __('Delete')],
								['confirm' => __('Are you sure you want to delete this price?')]);
						}
						if ($this->Authorize->can('refund', $event)) {
							echo $this->Html->link(__('Bulk Refunds'), ['controller' => 'Events', 'action' => 'refund', 'event' => $event->id, 'price' => $price->id]);
						}
					?></td>
				</tr>
<?php
		if (!empty($price->description)):
?>
				<tr>
					<td colspan="<?= 6 + $deposit ?>"><?= $price->description ?></td>
				</tr>
<?php
		endif;

		if ($price->has('canRegister')):
?>
				<tr>
					<td colspan="<?= 6 + $deposit ?>"><?= $this->Html->formatMessage($price->canRegister) ?></td>
				</tr>
<?php
		 endif;
	endforeach;
?>
			</tbody>
		</table>
		</div>
	</div>
</div>
<?php
endif;

if (!$this->Identity->isLoggedIn()) {
	echo $this->element('Events/not_logged_in');
} else {
	echo $this->element('messages', ['messages' => $notices]);
	if ($allowed) {
		echo $this->Html->tag('h2', $this->Html->link(__('Register now!'),
			['controller' => 'Registrations', 'action' => 'register', 'event' => $id],
			['title' => __('Register for {0}', $event->name), 'style' => 'text-decoration: underline;']
		));
	}
	if ($admin_register) {
		echo $this->Html->para('warning-message', __('Note that you have been given the option to register before the specified opening date due to your status as system administrator.'));
	}
}
?>

</div>

<?php
if (!empty($event->division->events) || !empty($event->alternate)):
?>
<div class="related row">
	<div class="column">
		<h4 class="subheader"><?= __('You might alternately be interested in the following registrations:') ?></h4>
		<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Registration') ?></th>
					<th><?= __('Type') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	if ($event->has('division')):
		foreach ($event->division->events as $related):
?>
				<tr>
					<td><?= $this->Html->link($related->name, ['controller' => 'Events', 'action' => 'view', 'event' => $related->id]) ?></td>
					<td><?= __($related->event_type->name) ?></td>
				</tr>
<?php
		endforeach;
	endif;

	foreach ($event->alternate as $related):
?>
				<tr>
					<td><?= $this->Html->link($related->name, ['controller' => 'Events', 'action' => 'view', 'event' => $related->id]) ?></td>
					<td><?= __($related->event_type->name) ?></td>
				</tr>

<?php
	endforeach;
?>
			</tbody>
		</table>
		</div>
	</div>
</div>
<?php
endif;
?>

<div class="actions columns">
	<?= $this->element('Events/actions', ['event' => $event, 'format' => 'list']) ?>
</div>

<?php
if (!empty($event->preregistrations) && $this->Authorize->can('add_preregistration', $event)):
?>
<div class="related row">
	<div class="column">
		<h4 class="subheader"><?= __('Preregistrations') ?></h4>
		<div class="table-responsive">
			<table class="table table-striped table-hover table-condensed">
				<thead>
					<tr>
						<th><?= __('Person') ?></th>
						<th class="actions"><?= __('Actions') ?></th>
					</tr>
				</thead>
				<tbody>
<?php
	foreach ($event->preregistrations as $preregistration):
?>
					<tr>
						<td><?= $this->element('People/block', ['person' => $preregistration->person]) ?></td>
						<td class="actions"><?php
							echo $this->Form->iconPostLink('delete_24.png',
								['controller' => 'Preregistrations', 'action' => 'delete', 'preregistration' => $preregistration->id],
								['alt' => __('Delete'), 'title' => __('Delete')],
								['confirm' => __('Are you sure you want to delete this preregistration?')]);
						?></td>
					</tr>

<?php
	endforeach;
?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<?php
endif;

echo $this->element('People/confirmation', ['fields' => ['height', 'shirt_size', 'year_started', 'skill_level']]);

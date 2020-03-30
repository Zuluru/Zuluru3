<?php
/**
 * @type \App\Model\Entity\League $league
 * @type boolean $competition
 * @type boolean $multi_day
 * @type string $id_field
 * @type int $id
 * @type \Cake\I18n\FrozenDate[] $week
 */

use Cake\Core\Configure;
?>

<tr>
	<th colspan="<?= 3 + $multi_day ?>"><a name="<?= $week[0]->toDateString() ?>"><?= $this->Time->dateRange($week[0], $week[1]) ?></a></th>
	<th colspan="<?= 2 + !$competition ?>" class="actions splash-action">
	<?= $this->Html->iconLink('field_24.png',
		['action' => 'slots', $id_field => $id, 'date' => $week[0]->toDateString()],
		['alt' => __(Configure::read("sports.{$league->sport}.fields_cap")), 'title' => __('Available {0}', __(Configure::read("sports.{$league->sport}.fields_cap")))]) ?>
	</th>
</tr>

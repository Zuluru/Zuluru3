<?php
/**
 * @type $league \App\Model\Entity\League
 * @type $competition boolean
 * @type $multi_day boolean
 * @type $id_field string
 * @type $id int
 * @type $week \Cake\I18n\FrozenDate[]
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

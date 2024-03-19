<?php
/**
 * @var \App\Model\Entity\Division $division
 * @var \App\Model\Entity\League $league
 */
?>
<tr>
	<th><?= __('Team Name') ?></th>
	<th><?= __('Rating') ?></th>
<?php
if ($league->hasSpirit()):
?>
	<th><?= __('Spirit') ?></th>
<?php
endif;
?>

</tr>

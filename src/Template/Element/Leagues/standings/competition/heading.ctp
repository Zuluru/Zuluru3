<?php
/**
 * @type $division \App\Model\Entity\Division
 * @type $league \App\Model\Entity\League
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

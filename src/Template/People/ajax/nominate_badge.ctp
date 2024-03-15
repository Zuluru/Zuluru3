<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Badge $badge
 */

$extra_url = ['controller' => 'People', 'action' => 'nominate_badge_reason', 'badge' => $badge->id];
if ($badge->category == 'assigned') {
	$extra_url = [__('Assign badge') => $extra_url];
} else {
	$extra_url = [__('Nominate for badge') => $extra_url];
}
echo $this->element('People/search_results', compact('extra_url'));

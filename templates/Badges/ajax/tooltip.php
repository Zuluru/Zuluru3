<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Badge $badge
 */
?>
<h2><?php
echo $this->Html->iconImg("{$badge->icon}_64.png",
		['class' => 'profile-photo']
);
echo $badge->name;
?></h2>
<p><?= $badge->description ?></p>
<dl class="dl-horizontal">
<dt><?= __('Awarded to') ?></dt>
<dd><?= count($badge->people) . ' ' . __('people') ?></dd>
</dl>

<p><?php
echo $this->Html->link(__('Details'), ['controller' => 'Badges', 'action' => 'view', '?' => ['badge' => $badge->id]]);
?></p>

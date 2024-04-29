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
<dl class="row">
<dt class="col-sm-2 text-end"><?= __('Awarded to') ?></dt>
<dd class="col-sm-10 mb-0"><?= count($badge->people) . ' ' . __('people') ?></dd>
</dl>

<p><?php
echo $this->Html->link(__('Details'), ['controller' => 'Badges', 'action' => 'view', '?' => ['badge' => $badge->id]]);
?></p>

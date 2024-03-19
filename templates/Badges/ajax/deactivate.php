<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Badge $badge
 */

echo $this->Jquery->ajaxLink(__('Activate'), ['url' => ['action' => 'activate', 'badge' => $badge->id]]);

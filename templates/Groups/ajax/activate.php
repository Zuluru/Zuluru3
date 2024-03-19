<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Group $group
 */

echo $this->Jquery->ajaxLink(__('Deactivate'), ['url' => ['action' => 'deactivate', 'group' => $group->id]]);

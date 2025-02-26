<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\UserGroup $group
 */

echo $this->Jquery->ajaxLink(__('Deactivate'), ['url' => ['action' => 'deactivate', '?' => ['group' => $group->id]]]);

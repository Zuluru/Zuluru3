<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\UserGroup $group
 */

echo $this->Jquery->ajaxLink(__('Activate'), ['url' => ['action' => 'activate', '?' => ['group' => $group->id]]]);

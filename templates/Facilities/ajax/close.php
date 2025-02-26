<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Facility $facility
 */

echo $this->Jquery->ajaxLink(__('Open'), ['url' => ['action' => 'open', '?' => ['facility' => $facility->id]]]);

<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Facility $facility
 */

echo $this->Jquery->ajaxLink(__('Close'), ['url' => ['action' => 'close', 'facility' => $facility->id]]);

<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Field $field
 */

echo $this->Jquery->ajaxLink(__('Close'), ['url' => ['action' => 'close', '?' => ['field' => $field->id]]]);

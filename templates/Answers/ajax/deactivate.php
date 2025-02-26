<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Answer $answer
 */

echo $this->Jquery->ajaxLink(__('Activate'), ['url' => ['action' => 'activate', '?' => ['answer' => $answer->id]]]);

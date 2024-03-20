<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Question $question
 */

echo $this->Jquery->ajaxLink(__('Activate'), ['url' => ['action' => 'activate', '?' => ['question' => $question->id]]]);

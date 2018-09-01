<?php
echo $this->Jquery->ajaxLink(__('Deactivate'), ['url' => ['action' => 'deactivate', 'question' => $question->id]]);

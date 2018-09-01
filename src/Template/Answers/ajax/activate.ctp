<?php
echo $this->Jquery->ajaxLink(__('Deactivate'), ['url' => ['action' => 'deactivate', 'answer' => $answer->id]]);

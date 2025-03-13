<?php
/**
 * @var \App\View\AppView $this
 * @var string $message
 */

?>
<?= $this->Html->scriptBlock("alert('$message');", ['buffer' => true]);

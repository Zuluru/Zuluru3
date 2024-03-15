<?php
/**
 * @var \App\View\AppView $this
 * @var string $message
 * @var string $twitter
 */

?>
<?= $this->Html->scriptBlock("zjQuery('#TwitterMessage').val('$twitter'); alert('$message');", ['buffer' => true]);

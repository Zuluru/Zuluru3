<?php
$this->Html->addCrumb(__('People'));
$this->Html->addCrumb(__('Act As'));
?>

<div class="people act_as">
<h2><?= __('Act As') ?></h2>

<?php
echo $this->Html->para(null, __('You are currently using the site as {0}. This gives you the option to change to one of the following people.', $this->UserCache->read('Person.full_name')));
echo $this->Html->para(null, __('Switch to:'));
?>
<ul>
<?php
foreach ($opts as $id => $name) {
	echo $this->Html->tag('li', $this->Html->link($name, ['controller' => 'People', 'action' => 'act_as', 'person' => $id]));
}
?>
</ul>
</div>
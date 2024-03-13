<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;
use App\Core\ModuleRegistry;
?>

<div class="zuluru">
	<p><?= __('Following are the various spirit questionnaire options, with a short description and preview of each.') ?></p>
<?php
// We need to save the existing form helper context and restore it later
$context = $this->Form->context();
echo $this->Form->create(false, ['align' => 'horizontal']);
$options = Configure::read('options.spirit_questions');
foreach ($options as $option => $name):
	$spirit_obj = ModuleRegistry::getInstance()->load("Spirit:{$option}");
?>
	<h2><?= $name ?></h2>
	<div class="pseudo_form">
<?php
	echo $this->Html->para(null, __($spirit_obj->description));
	echo $this->element('Spirit/legend', compact('spirit_obj'));
	echo $this->element('FormBuilder/input', ['prefix' => null, 'preview' => true, 'questions' => $spirit_obj->questions]);
?>
	</div>
<?php
endforeach;

echo $this->Form->end();
$this->Form->context($context);
?>
</div>

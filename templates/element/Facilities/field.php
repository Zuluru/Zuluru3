<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Facility $facility
 * @var int $index
 */

use Cake\Core\Configure;
if ($facility->has('fields') && array_key_exists($index, $facility->fields)) {
	$errors = $facility->fields[$index]->getErrors();
	$new = false;
} else {
	$new = true;
}
$collapsed = (empty($errors) && !$facility->isNew());

$this->start('field_details');

if (!$new) {
	echo $this->Form->control("fields.$index.id");
}
// TODO: Add default value based on the facility's primary sport?
echo $this->Form->control("fields.$index.sport", [
	'options' => Configure::read('options.sport'),
	'hide_single' => true,
	'empty' => '---',
	'help' => __('Sport played at this {0}.', Configure::read('UI.field')),
	'duplicate_help' => true,
]);
if ($new) {
	// This is because of how CakePHP handles checkboxes differently than everything else.
	echo $this->Form->control("fields.$index.is_open", [
		'checked' => true,
		'duplicate_help' => true,
	]);
} else {
	echo $this->Form->control("fields.$index.is_open", [
		'duplicate_help' => true,
	]);
}
echo $this->Form->control("fields.$index.indoor", [
	'duplicate_help' => true,
]);
echo $this->Form->control("fields.$index.surface", [
	'options' => Configure::read('options.surface'),
	'empty' => '---',
	'hide_single' => true,
	'duplicate_help' => true,
]);
echo $this->Form->control("fields.$index.rating", [
	'options' => Configure::read('options.field_rating'),
	'empty' => '---',
	'hide_single' => true,
	'duplicate_help' => true,
]);
echo $this->Form->control("fields.$index.layout_url", [
	'help' => __('Optional link to a page (probably an image) with a view of the layout. Intended to be used only if the built-in layout editor is insufficient.'),
	'duplicate_help' => true,
]);

$this->end();

echo $this->Accordion->panel(
	$this->Accordion->panelHeading("Field{$index}", __('{0} Details', Configure::read('UI.field_cap')), [
		'extraContent' => $this->Form->i18nControls("fields.$index.num", [
			'label' => __('Number'),
			'placeholder' => __('{0} Number', Configure::read('UI.field_cap')),
			'duplicate_help' => true,
		]),
	]),
	$this->Accordion->panelContent("Field{$index}", $this->fetch('field_details'))
);

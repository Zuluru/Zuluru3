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
?>

<div class="panel panel-default">
	<div class="panel-heading" role="tab" id="FieldHeading<?= $index ?>">
		<h4 class="panel-title"><a role="button" class="accordion-toggle<?= $collapsed ? ' collapsed' : '' ?>" data-toggle="collapse" data-parent="#accordion" href="#FieldDetails<?= $index ?>" aria-expanded="<?= $collapsed ? 'true' : 'false' ?>" aria-controls="FieldDetails<?= $index ?>"><?= __('{0} Details', Configure::read('UI.field_cap')) ?>:</a>
			<?= $this->Form->i18nControls("fields.$index.num", [
				'label' => __('Number'),
				'placeholder' => __('{0} Number', Configure::read('UI.field_cap')),
				'duplicate_help' => true,
			]) ?>
		</h4>
	</div>
	<div id="FieldDetails<?= $index ?>" class="panel-collapse collapse<?= $collapsed ? '' : ' in' ?>" role="tabpanel" aria-labelledby="FieldHeading<?= $index ?>">
		<div class="panel-body">
<?php
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
?>
		</div>
	</div>
</div>

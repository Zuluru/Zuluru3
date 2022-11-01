<?php
/**
 * @type $person \App\Model\Entity\Person
 * @type $upload \App\Model\Entity\Upload
 */

$this->Html->addCrumb(__('People'));
$this->Html->addCrumb($person->full_name);
$this->Html->addCrumb(__('Upload Photo'));
?>

<div class="people upload">
<h2><?= __('Upload Photo') . ': ' . $person->full_name ?></h2>

<?= $this->element('People/photo_legal') ?>

<?php
echo $this->Html->para(null, __('Supported formats are PNG, JPEG and GIF.'));

// This is intentionally outside the form, so that the file contents aren't uploaded.
// That will be taken care of by the plugin and browser instead, sending only the
// cropped data.
echo $this->Form->input('photo', ['type' => 'file', 'label' => false, 'value' => __('Choose a file'), 'accept' => 'image/*']);

echo $this->Html->tag('div', $this->Html->para('', __('Select an image to upload.')), ['id' => 'croppie-msg', 'class' => 'step1']);
echo $this->Html->para('step2', __('Drag the image, and use the slider to resize, until the desired area is highlighted.'));
echo $this->Html->tag('div', $this->Html->tag('div', '', ['id' => 'croppie']), ['id' => 'croppie-wrap', 'class' => 'step2']);

$this->Html->css('https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.2/croppie.min.css', ['block' => true]);
$this->Html->script('https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.2/croppie.min.js', ['block' => true]);
$this->Html->scriptBlock('
	var uploadCrop;

	function readFile(input) {
		if (input.files && input.files[0]) {
			var reader = new FileReader();

			reader.onload = function (e) {
				uploadCrop.croppie("bind", {
					url: e.target.result
				});
				jQuery(".step1").hide();
				jQuery(".step2").show();
			}

			reader.readAsDataURL(input.files[0]);
		} else {
			alert("Sorry - your browser doesn\'t support the FileReader API");
		}
	}

	uploadCrop = jQuery("#croppie").croppie({
		viewport: {
			width: 150,
			height: 150
		},
		enableOrientation: true,
		enableExif: true
	});

	jQuery("#photo").on("change", function () { readFile(this); });
	jQuery("#UploadButton").on("click", function (ev) {
		uploadCrop.croppie("result", {
			type: "canvas",
			size: "viewport"
		}).then(function (croppedData) {
			jQuery("#CroppedData").val(croppedData);
		});
	});
	jQuery("#RotateLeftButton").on("click", function (ev) {
		uploadCrop.croppie("rotate", 90);
		return false;
	});
	jQuery("#RotateRightButton").on("click", function (ev) {
		uploadCrop.croppie("rotate", -90);
		return false;
	});
', ['block' => true]);

echo $this->Form->create($upload, ['align' => 'horizontal', 'type' => 'file']);
echo $this->Form->hidden('person_id', ['value' => $person->id]);
echo $this->Form->hidden('cropped', ['id' => 'CroppedData']);
$this->Form->unlockField('cropped');
echo $this->Form->button(__('Rotate Left'), ['class' => 'step2', 'id' => 'RotateLeftButton']);
echo $this->Form->button(__('Rotate Right'), ['class' => 'step2', 'id' => 'RotateRightButton']);
echo $this->Form->button(__('Upload'), ['class' => 'btn-success step2', 'id' => 'UploadButton']);
echo $this->Form->end();
?>

</div>

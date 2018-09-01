<?php
use Html2Text\Html2Text;
use Html2Text\Html2TextException;

$response = [];
foreach($questions as $id => $question) {
	$question = trim($question);
	if (strpos($question, '<') !== false) {
		try {
			$question = Html2Text::convert($question);
		} catch (Html2TextException $ex) {
			// If there's a parsing exception, just return the HTML.
		}
	}
	$question = $this->Text->truncate($question, 250);
	$response[] = [
		'label' => $question,
		'value' => $id,
	];
}
echo json_encode($response);

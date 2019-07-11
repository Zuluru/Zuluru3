<?php
use Cake\Core\Configure;
use Cake\Utility\Inflector;
?>

<div class="help">
<?php
$elements = ['Help'];
$heading = ZULURU;
if (isset($controller)) {
	$elements[] = $controller;
	$heading = $controller;
	if (isset($topic)) {
		$elements[] = $topic;
		$heading = null;
		if (isset($item)) {
			$elements[] = $item;
			if (isset($subitem)) {
				$elements[] = $subitem;
			}
		}
	}
}

foreach ($elements as $element) {
	$this->Html->addCrumb(__(Inflector::humanize($element)));
}

if ($heading !== null) {
	echo $this->Html->tag('h2', __('{0} Help', __(Inflector::humanize($heading))));
	$elements[] = 'index';
}
echo $this->element(implode('/', $elements));

if (isset($controller) && !isset($topic)) {
	echo $this->Html->para(null, $this->Html->link(__('Return to main help page'), ['controller' => 'Help']));
}

$body = htmlspecialchars(__('I have a suggestion for the {0} online help page at {1}', ZULURU, implode(' : ', $elements)));
?>

<hr>
<p><?= __('If you have suggestions for additions, changes or other improvements to this online help, please send them to {0}.',
	$this->Html->link(Configure::read('email.support_email'), 'mailto:' . Configure::read('email.support_email') . '?subject=' . ZULURU . "%20Online%20Help%20Suggestion&body=$body")
);
?></p>
</div>

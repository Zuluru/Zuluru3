<?php
use Cake\Core\Configure;
use Cake\Utility\Text;

$str = strtolower($colour);

// Look for some common patterns; we want to reduce the input
// down to things with + ("and") or / ("or") in it
$str = preg_replace(
	[
		'/[\!,\.:]/',			// 1. toss out useless punctuation
		'/\bw\//',				// 2. w/ is abbreviation for with
		'/\(\s*with (.*)\)/',	// 3. anything with "with" or "and" in parentheses
		'/\(\s*and (.*)\)/',	//    gets replaced with a +
		'/\(\s*or (.*)\)/',		// 4. anything with "or" or "alt" or "alternate" or "backup"
		'/\(\s*alt (.*)\)/',	//    in parentheses gets replaced with a /
		'/\(\s*alternate (.*)\)/',
		'/\(\s*backup (.*)\)/',
		'/\s*\(.*?\)\s*/',		// 5. anything else in parentheses is removed
		'/\bhome\b/',			// 6. as is home
		'/\bmaison\b/',			//
		'/\baway\b/',			//    or away
		'/\bvisiteur\b/',		//
		'/\bwith\b/',			// 7. anything with "with" or "and" or "&"
		'/\band\b/',			//    gets replaced with a +
		'/\s*&\s*/',
		'/\bor\b/',				// 8. anything with "or" or "alt" or "alternate" or "backup"
		'/\balt\b/',			//    gets replaced with a /
		'/\balternate\b/',
		'/\bbackup\b/',
	],
	[
		' ',		// 1
		' with ',	// 2
		'+$1',		// 3
		'+$1',
		'/$1',		// 4
		'/$1',
		'/$1',
		'/$1',
		' ',		// 5
		' ',		// 6
		' ',
		' ',
		' ',
		'+',		// 7
		'+',
		'+',
		'/',		// 8
		'/',
		'/',
		'/',
	], $str);

$output = [];

// Teams list their various colour options separated by things that we've reduced to /
$options = explode('/', $str);
foreach ($options as $option) {
	// Within each option, there might be a combination, like "red with blue", separated
	// by things that we've recuded to +
	$option_output = [];
	$combinations = explode('+', trim($option));
	foreach ($combinations as $combination) {
		$words = array_map('trim', explode(' ', trim($combination)));
		$found = false;

		// Keep dropping words off the end until we find something we recognize
		while (!empty($words)) {
			$file = Text::slug(implode(' ', $words), '_');
			if (file_exists (Configure::read('App.paths.imgBase') . DS . 'shirts' . DS . $file . '.png')) {
				$found = $file;
				break;
			}
			array_pop($words);
		}

		if ($found) {
			// If we found something, use it
			$option_output[] = $this->Html->iconImg("shirts/$file.png");
		} else {
			// Let's try again, one word at a time
			$words = array_map('trim', explode(' ', trim($combination)));
			foreach ($words as $word) {
				$file = Text::slug($word, '_');
				if (file_exists (Configure::read('App.paths.imgBase') . DS . 'shirts' . DS . $file . '.png')) {
					$option_output[] = $this->Html->iconImg("shirts/$file.png");
					break;
				}
			}

			// If we found nothing we recognized, we don't want to continue
			if (empty($option_output)) {
				// Make sure there's a default icon here
				$option_output[] = $this->Html->iconImg('shirts/default.png');
			}
			break;
		}
	}

	// Put the combinations back together
	$output[] = implode('+', $option_output);
}

echo $this->Html->tag('span', implode('/', $output), ['title' => __('Shirt Colour') . ': ' . $colour, 'style' => 'white-space: nowrap;']);

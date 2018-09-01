<p><?= __('Spirit symbols: ');
$min = $spirit_obj->mins();
$max = $spirit_obj->maxs();
$range = $max - $min;

$ratios = array_reverse($spirit_obj->ratios, true);
$files = array_keys($ratios);
$lows = array_values($ratios);

$highs = $lows;
array_shift($highs);
$highs[] = 1;

$ranges = [];
foreach ($files as $key => $file) {
	$low = rtrim(rtrim(sprintf('%.1f', $range * $lows[$key]), '0'), '.');
	$high = rtrim(rtrim(sprintf('%.1f', $range * $highs[$key]), '0'), '.');
	$ranges[] = "$low-$high: " . $this->Html->iconImg("spirit_$file.png");
}
echo implode('&nbsp;&nbsp;', $ranges);
?></p>

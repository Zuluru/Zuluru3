<?php
/**
 * @type \App\View\AppView $this
 * @type \App\Model\Entity\Plugin[] $plugins
 */

$this->Html->addCrumb(__('Plugins'));
$this->Html->addCrumb(__('List'));
?>

<div class="plugins index">
	<h2><?= __('Plugins') ?></h2>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Name') ?></th>
					<th><?= __('Description') ?></th>
					<th><?= __('Site') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
foreach ($plugins as $plugin):
	$yamlfile = ROOT . DS . $plugin->path . DS . 'zuluru.yaml';
	if (file_exists($yamlfile)) {
		$yaml = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($yamlfile));
	} else {
		$yaml = false;
	}
?>
				<tr>
					<td><?= h($plugin->name) ?></td>
					<td><?= $yaml ? $yaml['plugin']['description'] : '' ?></td>
					<td><?= $yaml ? $this->Html->link($yaml['plugin']['url'], $yaml['plugin']['url'], ['target' => '_new']) : '' ?></td>
					<td class="actions"><?php
						if ($plugin->enabled) {
							echo $this->Jquery->ajaxLink(__('Deactivate'), [
								'url' => ['action' => 'deactivate', 'plugin_id' => $plugin->id],
								'disposition' => 'replace_closest',
								'selector' => 'td',
							]);

							if ($yaml && $yaml['plugin']['settings']) {
								echo $this->Html->link(__('Settings'), ['plugin' => $plugin->load_name, 'controller' => 'Settings', 'action' => 'index']);
							}
						} else {
							echo $this->Html->link(__('Activate'), ['action' => 'activate', 'plugin_id' => $plugin->id]);
						}
					?></td>
				</tr>

<?php
endforeach;
?>
			</tbody>
		</table>
	</div>
</div>

<td class="actions"><?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Plugin $plugin
 */

echo $this->Html->link(__('Activate'), ['action' => 'activate', '?' => ['plugin_id' => $plugin->id]]);
?></td>

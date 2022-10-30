<td class="actions"><?php
/**
 * @type $this \App\View\AppView
 * @type $plugin \App\Model\Entity\Plugin
 */

echo $this->Html->link(__('Activate'), ['action' => 'activate', 'plugin_id' => $plugin->id]);
?></td>

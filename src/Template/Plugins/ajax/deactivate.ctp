<td class="actions"><?php
/**
 * @type \App\View\AppView $this
 * @type $plugin \App\Model\Entity\Plugin
 */

echo $this->Html->link(__('Activate'), ['action' => 'activate', 'plugin_id' => $plugin->id]);
?></td>

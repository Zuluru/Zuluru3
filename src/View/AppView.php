<?php
namespace App\View;

use App\View\Helper\AuthorizeHelper;
use Cake\View\View;

/**
 * App View class
 *
 * @property AuthorizeHelper $Authorize
 * TODO: Add @type $this \App\View\AppView to all templates
 */
class AppView extends View {

    /**
     * Initialization hook method.
     *
     * For e.g. use this method to load a helper for all views:
     * `$this->loadHelper('Html');`
     *
     * @return void
     */
    public function initialize() {
		$this->loadHelper('Authentication.Identity');
		$this->loadHelper('Authorize');
		$this->loadHelper('UserCache');
		$this->loadHelper('Number');
		$this->loadHelper('Text');
		$this->loadHelper('Html', ['className' => 'ZuluruHtml']);
		$this->loadHelper('Form', ['className' => 'ZuluruForm']);
		$this->loadHelper('Time', ['className' => 'ZuluruTime']);
		$this->loadHelper('Game', ['className' => 'ZuluruGame']);
		$this->loadHelper('BootstrapUI.Flash');
		$this->loadHelper('BootstrapUI.Paginator', ['templates' => 'paginator-templates']);
		$this->loadHelper('ZuluruBootstrap.Accordion');
		$this->loadHelper('ZuluruJquery.Jquery');
		//$this->loadHelper('Less.Less'); // required for parsing less files
    }

}

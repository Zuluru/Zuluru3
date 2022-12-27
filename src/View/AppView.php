<?php
namespace App\View;

use App\View\Helper\AuthorizeHelper;
use App\View\Helper\UserCacheHelper;
use App\View\Helper\ZuluruFormHelper;
use App\View\Helper\ZuluruGameHelper;
use App\View\Helper\ZuluruHtmlHelper;
use App\View\Helper\ZuluruTimeHelper;
use Authentication\View\Helper\IdentityHelper;
use Cake\View\View;

/**
 * App View class
 *
 * @property IdentityHelper $Identity
 * @property AuthorizeHelper $Authorize
 * @property UserCacheHelper $UserCache
 * @property ZuluruFormHelper $Form
 * @property ZuluruGameHelper $Game
 * @property ZuluruHtmlHelper $Html
 * @property ZuluruTimeHelper $Time
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

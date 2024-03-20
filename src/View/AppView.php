<?php
namespace App\View;

use App\View\Helper\AuthorizeHelper;
use App\View\Helper\SelectorHelper;
use App\View\Helper\UserCacheHelper;
use App\View\Helper\ZuluruBreadcrumbsHelper;
use App\View\Helper\ZuluruFormHelper;
use App\View\Helper\ZuluruGameHelper;
use App\View\Helper\ZuluruHtmlHelper;
use App\View\Helper\ZuluruTimeHelper;
use Authentication\View\Helper\IdentityHelper;
use BootstrapUI\View\Helper\FlashHelper;
use BootstrapUI\View\Helper\PaginatorHelper;
use Cake\View\Helper\NumberHelper;
use Cake\View\Helper\TextHelper;
use Cake\View\View;
use ZuluruBootstrap\View\Helper\AccordionHelper;
use ZuluruJquery\View\Helper\JqueryHelper;

/**
 * App View class
 *
 * @property IdentityHelper $Identity
 * @property AuthorizeHelper $Authorize
 * @property UserCacheHelper $UserCache
 * @property NumberHelper $Number
 * @property TextHelper $Text
 * @property SelectorHelper $Selector
 * @property ZuluruHtmlHelper $Html
 * @property ZuluruFormHelper $Form
 * @property ZuluruBreadcrumbsHelper $Breadcrumbs
 * @property ZuluruTimeHelper $Time
 * @property ZuluruGameHelper $Game
 * @property FlashHelper $Flash
 * @property PaginatorHelper $Paginator
 * @property AccordionHelper $Accordion
 * @property JqueryHelper $Jquery
 */
class AppView extends View {

	/**
	 * Initialization hook method.
	 *
	 * Use this method to add common initialization code like adding helpers.
	 *
	 * e.g. `$this->addHelper('Html');`
	 *
	 * @return void
	 */
	public function initialize(): void {
		$this->loadHelper('Authentication.Identity');
		$this->loadHelper('Authorize');
		$this->loadHelper('UserCache');
		$this->loadHelper('Number');
		$this->loadHelper('Text');
		$this->loadHelper('Selector');
		$this->loadHelper('Html', ['className' => 'ZuluruHtml']);
		$this->loadHelper('Form', ['className' => 'ZuluruForm']);
		$this->loadHelper('Breadcrumbs', ['className' => 'ZuluruBreadcrumbs']);
		$this->loadHelper('Time', ['className' => 'ZuluruTime']);
		$this->loadHelper('Game', ['className' => 'ZuluruGame']);
		$this->loadHelper('BootstrapUI.Flash');
		$this->loadHelper('BootstrapUI.Paginator', ['templates' => 'paginator-templates']);
		$this->loadHelper('ZuluruBootstrap.Accordion');
		$this->loadHelper('ZuluruJquery.Jquery');
		//$this->loadHelper('Less.Less'); // required for parsing less files
	}

}

<?php
/**
 * ownCloud - weather
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Loic Blot <loic.blot@unix-experience.fr>
 * @copyright Loic Blot 2015
 */


namespace OCA\Weather\AppInfo;

use \OCP\AppFramework\App;
use Psr\Container\ContainerInterface;

use OCA\Weather\Controller\CityController;
use OCA\Weather\Controller\SettingsController;
use OCA\Weather\Controller\WeatherController;

use OCA\Weather\Db\CityMapper;
use OCA\Weather\Db\SettingsMapper;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\IUserSession;
use OC\User\Session;

class Application extends App implements IBootstrap {

	public function __construct (array $urlParams = []) {
		parent::__construct('weather', $urlParams);
    }

	public function register(IRegistrationContext $context): void {
		
		$context->registerService('UserId', function(ContainerInterface $c) {
			/** @var Session */
			$userSession = $c->get(IUserSession::class);

			return $userSession->getUser() ? $userSession->getUser()->getUID() : null;
		});

		$context->registerService('Config', function(ContainerInterface $c) {
			return $c->get('ServerContainer')->getConfig();
		});

		$context->registerService('L10N', function(ContainerInterface $c) {
		return $c->get('ServerContainer')->getL10N($c->get('AppName'));
		});

		/**
		 * Database Layer
		 */
		$context->registerService('CityMapper', function(ContainerInterface $c) {
			return new CityMapper($c->get('ServerContainer')->getDatabaseConnection());
		});

		$context->registerService('SettingsMapper', function(ContainerInterface $c) {
			return new SettingsMapper($c->get('ServerContainer')->getDatabaseConnection());
		});

		/**
		 * Controllers
		 */
		$context->registerService('CityController', function(ContainerInterface $c) {
			return new CityController(
				$c->get('AppName'),
				$c->get('Config'),
				$c->get('Request'),
				$c->get('UserId'),
				$c->get('CityMapper'),
				$c->get('SettingsMapper')
			);
		});

		$context->registerService('SettingsController', function(ContainerInterface $c) {
			return new SettingsController(
				$c->get('AppName'),
				$c->get('Config'),
				$c->get('Request'),
				$c->get('UserId'),
				$c->get('SettingsMapper'),
				$c->get('CityMapper')
			);
		});

		$context->registerService('WeatherController', function(ContainerInterface $c) {
			return new WeatherController(
				$c->get('AppName'),
				$c->get('Config'),
				$c->get('Request'),
				$c->get('UserId'),
				$c->get('CityMapper'),
				$c->get('SettingsMapper'),
				$c->get('L10N')
			);
		});
	}

	public function boot(IBootContext $context): void {
	}
}

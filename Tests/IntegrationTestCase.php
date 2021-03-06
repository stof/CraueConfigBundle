<?php

namespace Craue\ConfigBundle\Tests;

use Craue\ConfigBundle\Entity\Setting;
use Craue\ConfigBundle\Twig\Extension\ConfigTemplateExtension;
use Craue\ConfigBundle\Util\Config;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2015 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
abstract class IntegrationTestCase extends WebTestCase {

	/**
	 * @var Client
	 */
	protected $client;

	/**
	 * @var boolean
	 */
	private static $databaseInitialized = false;

	/**
	 * {@inheritDoc}
	 */
	protected function setUp() {
		$this->client = static::createClient();

		if (!self::$databaseInitialized) {
			$this->rebuildDatabase();
			self::$databaseInitialized = true;
		}

		$this->removeAllSettings();
	}

	/**
	 * {@inheritDoc}
	 */
	protected static function createKernel(array $options = array()) {
		$environment = isset($options['environment']) ? $options['environment'] : 'test';
		$configFile = isset($options['config']) ? $options['config'] : 'config.yml';

		if (class_exists('Craue\ConfigBundle\Tests\LocalAppKernel')) {
			return new LocalAppKernel($environment, $configFile);
		}

		return new AppKernel($environment, $configFile);
	}

	protected function rebuildDatabase() {
		$em = $this->getEntityManager();
		$metadata = $em->getMetadataFactory()->getAllMetadata();
		$schemaTool = new SchemaTool($em);

		$schemaTool->dropSchema($metadata);
		$schemaTool->createSchema($metadata);
	}

	/**
	 * Persists a setting.
	 * @param string $name
	 * @param string|null $value
	 * @param string|null $section
	 * @return Setting
	 */
	protected function persistSetting($name, $value = null, $section = null) {
		$setting = new Setting();
		$setting->setName($name);
		$setting->setValue($value);
		$setting->setSection($section);

		$em = $this->getEntityManager();
		$em->persist($setting);
		$em->flush();

		return $setting;
	}

	/**
	 * Removes all settings.
	 */
	protected function removeAllSettings() {
		$em = $this->getEntityManager();

		foreach ($this->getSettingsRepo()->findAll() as $entity) {
			$em->remove($entity);
		}

		$em->flush();
	}

	/**
	 * @return Config
	 */
	protected function getConfig() {
		return $this->getService('craue_config');
	}

	/**
	 * @return ConfigTemplateExtension
	 */
	protected function getConfigTemplateExtension() {
		return $this->getService('twig.extension.craue_config_template');
	}

	/**
	 * @return EntityManager
	 */
	protected function getEntityManager() {
		return $this->getService('doctrine')->getManager();
	}

	/**
	 * @return EntityRepository
	 */
	protected function getSettingsRepo() {
		return $this->getEntityManager()->getRepository(get_class(new Setting()));
	}

	/**
	 * @return \Twig_Environment
	 */
	protected function getTwig() {
		return $this->getService('twig');
	}

	/**
	 * @param string $id The service identifier.
	 * @return object The associated service.
	 */
	protected function getService($id) {
		return static::$kernel->getContainer()->get($id);
	}

	/**
	 * @param Client $client
	 * @param string $route
	 * @param array $parameters
	 * @param boolean $absolute
	 * @return string URL
	 */
	protected function url(Client $client, $route, array $parameters = array(), $absolute = false) {
		return $client->getContainer()->get('router')->generate($route, $parameters, $absolute);
	}

	/**
	 * @param Client $client
	 * @param string $expectedTargetUrl
	 */
	protected function assertRedirect(Client $client, $expectedTargetUrl) {
		// don't just check with $client->getResponse()->isRedirect() to know the actual URL on failure
		$this->assertEquals(302, $client->getResponse()->getStatusCode());
		$this->assertContains($expectedTargetUrl, $client->getResponse()->headers->get('Location'));
	}

}

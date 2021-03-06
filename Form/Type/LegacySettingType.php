<?php

namespace Craue\ConfigBundle\Form\Type;

/**
 * for Symfony 2.7
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2015 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class LegacySettingType extends AbstractSettingType {

	/**
	 * {@inheritDoc}
	 */
	public function getName() {
		return 'craue_config_setting';
	}

}

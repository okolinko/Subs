<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/26/16
 * Time: 6:01 PM
 */

namespace Toppik\Subscriptions\Model\Settings;

use Magento\Framework\Model\AbstractModel;
use Toppik\Subscriptions\Api\Data\Settings\UnitInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Unit extends AbstractModel implements UnitInterface, IdentityInterface
{

    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'subscriptions_settings_unit';

    /**
     * @var string
     */
    protected $_cacheTag = 'subscriptions_settings_unit';

    protected $_eventPrefix = 'subscriptions_settings_unit';

    /**
     * Initialize resource model
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Toppik\Subscriptions\Model\ResourceModel\Settings\Unit');
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @return integer
     */
    public function getLength()
    {
        return $this->getData(self::LENGTH);
    }

    /**
     * @param string $title
     * @return UnitInterface
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @param integer $length
     * @return UnitInterface
     */
    public function setLength($length)
    {
        return $this->setData(self::LENGTH, $length);
    }
}
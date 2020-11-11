<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/26/16
 * Time: 5:45 PM
 */

namespace Toppik\Subscriptions\Api\Data\Settings;


interface UnitInterface
{

    const UNIT_ID = 'unit_id';
    const TITLE = 'title';
    const LENGTH = 'length';

    /**
     * @return integer
     */
    public function getId();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return integer
     */
    public function getLength();

    /**
     * @param integer $id
     * @return UnitInterface
     */
    public function setId($id);

    /**
     * @param string $title
     * @return UnitInterface
     */
    public function setTitle($title);

    /**
     * @param integer $length
     * @return UnitInterface
     */
    public function setLength($length);

}
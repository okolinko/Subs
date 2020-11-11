<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 10/12/16
 * Time: 7:32 PM
 */

namespace Toppik\Subscriptions\Migration\Step\CreditCards;

use Migration\ResourceModel\Source;

class Helper
{

    private $tableName = 'aw_sarp2_profile';

    private $perPage = 450;

    /**
     * @var Source
     */
    private $source;

    /**
     * @var int
     */
    private $referenceCount;

    /**
     * Helper constructor.
     * @param Source $source
     */
    public function __construct(Source $source)
    {
        $this->source = $source;
    }

    /**
     * @return int
     */
    public function getTotalPages() {
        return ceil($this->getCount() / $this->perPage);
    }

    /**
     * @return int
     */
    public function getCount() {
        if(is_null($this->referenceCount)) {
            $this->referenceCount = (int) $this->source->getRecordsCount($this->tableName, true, ['reference_id', ]);
        }
        return $this->referenceCount;
    }

    /**
     * @param int $pageNumber
     * @return array
     */
    public function getReferences($pageNumber)
    {
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->source->getAdapter();
        $select = $adapter->getSelect();
        $select
            ->from($this->tableName, 'reference_id')
            ->group('reference_id')
            ->order('entity_id ASC')
            ->limitPage($pageNumber, $this->perPage);
        return $adapter->loadDataFromSelect($select);
    }

}
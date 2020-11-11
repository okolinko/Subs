<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 10/7/16
 * Time: 8:02 PM
 */

namespace Toppik\Subscriptions\Plugin\Magento\Ui\Component\Filters\Type;

use Magento\Ui\Component\Filters\Type\Range as RangeFilter;
use Toppik\Subscriptions\Model\Settings;
use Magento\Framework\Api\FilterBuilder;

class Range
{

    /**
     * @var Settings\UnitFactory
     */
    private $unitFactory;
    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * Range constructor.
     * @param Settings\UnitFactory $unitFactory
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        Settings\UnitFactory $unitFactory,
        FilterBuilder $filterBuilder
    )
    {
        $this->unitFactory = $unitFactory;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * @param RangeFilter $range
     * @param $result
     * @return mixed
     */
    public function afterPrepare(RangeFilter $range, $result) {
        $filterData = $range->getContext()->getFiltersParams();

        if (isset($filterData[$range->getName()])) {
            $value = $filterData[$range->getName()];

            if (isset($value['from_length']) and is_numeric($value['from_length']) and isset($value['from_unit'])) {
                $unitLength = $this->getUnitLength($value['from_unit']);
                if($unitLength !== false) {
                    $length = $unitLength * $value['from_length'];
                    $this->applyFilterByType($range, 'gteq', $length);
                }
            }

            if (isset($value['to_length']) and is_numeric($value['to_length']) and isset($value['to_unit'])) {
                $unitLength = $this->getUnitLength($value['to_unit']);
                if($unitLength !== false) {
                    $length = $unitLength * $value['to_length'];
                    $this->applyFilterByType($range, 'lteq', $length);
                }
            }
        }
        return $result;
    }

    /**
     * Apply filter by its type
     *
     * @param RangeFilter $range
     * @param string $type
     * @param string $value
     */
    protected function applyFilterByType(RangeFilter $range, $type, $value)
    {
        if (!empty($value) && $value !== '0') {
            $filter = $this->filterBuilder->setConditionType($type)
                ->setField($range->getName())
                ->setValue($value)
                ->create();

            $range->getContext()->getDataProvider()->addFilter($filter);
        }
    }

    /**
     * @param $unitId
     * @return bool|int
     */
    protected function getUnitLength($unitId) {
        /* @var Settings\Unit $unit */
        $unit = $this->unitFactory->create();
        $unit->load($unitId);
        if(! $unit->getId()) {
            return false;
        }
        return $unit->getLength();
    }

}
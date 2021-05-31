<?php
/**
 * RegionProvider
 *
 * @copyright Copyright © 2021 Staempfli AG. All rights reserved.
 * @author    juan.alonso@staempfli.com
 */

namespace Magento\Customer\ViewModel\Address;

use Magento\Directory\Helper\Data as DataHelper;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class RegionProvider implements ArgumentInterface
{


    protected $regions = [];

    /**
     * @var DataHelper
     */
    private $directoryHelper;
    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;


    /**
     * Constructor
     *
     * @param DataHelper $directoryHelper
     * @param JsonSerializer $jsonSerializer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        DataHelper $directoryHelper,
        JsonSerializer $jsonSerializer
    ) {
        $this->directoryHelper= $directoryHelper;
        $this->jsonSerializer = $jsonSerializer;
    }

    public function getRegionJson() : string
    {
        $regions = $this->getRegions();
        return $this->jsonSerializer->serialize($regions);
    }


    /**
     * @return array
     */
    protected function getRegions() : array
    {
        if (!$this->regions) {
            $regions = $this->directoryHelper->getRegionData();
            $this->regions['config'] = $regions['config'];
            unset($regions['config']);
            foreach ($regions as $countryCode => $countryRegions) {
                foreach ($countryRegions as $regionId => $regionData) {
                    $this->regions[$countryCode][] = [
                        'id'   => $regionId,
                        'name' => $regionData['name'],
                        'code' => $regionData['code']
                    ];
                }
            }

        }
        return $this->regions;
    }

}

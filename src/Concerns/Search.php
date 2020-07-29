<?php

namespace Revolution\Amazon\ProductAdvertising\Concerns;

use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsRequest;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsResource;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsResponse;
use Illuminate\Support\Facades\Config;

trait Search
{
    /**
     * {@inheritdoc}
     */
    public function search(string $category, string $keyword = null, int $page = 1, array $parameters = null)
    {
        /**
         * @var SearchItemsResource[] $resources
         */
        $resources = SearchItemsResource::getAllowableEnumValues();

        $request = new SearchItemsRequest();
        $request->setSearchIndex($category);
        $request->setKeywords($keyword);
        $request->setItemPage($page);
        $request->setPartnerTag(Config::get('amazon-product.associate_tag'));
        $request->setPartnerType(PartnerType::ASSOCIATES);
        $request->setResources($resources);

        if (!empty($parameters)) {
            $setters = $request->setters();
            foreach ($parameters as $key => $value) {
                if (isset($setters[$key])) {
                    $setter = $setters[$key];
                    $request->$setter($value);
                }
            }
        }

        $request = $this->callHook('search', $request);

        /**
         * @var SearchItemsResponse $response
         */
        $response = $this->api()->searchItems($request);

        return json_decode($response->__toString(), true);
    }
}

<?

namespace Kit\MultiRegions\Catalog\Product;

use Bitrix\Catalog\Config\State;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Sale\Result;

class CatalogProvider extends \Bitrix\Catalog\Product\CatalogProvider
{
	/**
	 * @param array $products
	 *
	 * @return Result
	 */
	public function getProductData(array $products)
	{
		$oResult = parent::getProductData($products);

		$arData = $oResult->getData();
		foreach ($arData['PRODUCT_DATA_LIST'] as $k => $v) {
			$arStoreData = \CKitMultiRegions::getStoreQuantityForCurrentDomain($k);
			foreach ($v['PRICE_LIST'] as $k1 => $v1) {
				if ($v1['QUANTITY'] > $arStoreData['QUANTITY']) {
					$arData['PRODUCT_DATA_LIST'][$k]['CAN_BUY'] = "N";
				} else {
					$arData['PRODUCT_DATA_LIST'][$k]['CAN_BUY'] = "Y";
				}
			}
		}
		$oResult->setData($arData);
		return $oResult;
	}

	/**
	 * @param array $products
	 *
	 * @return Result
	 */
	public function getAvailableQuantity(array $products)
	{
		$oResult = parent::getAvailableQuantity($products);
		$arData = $oResult->getData();
		foreach ($arData['AVAILABLE_QUANTITY_LIST'] as $k => $v) {
			$arStoreData = \CKitMultiRegions::getStoreQuantityForCurrentDomain($k);
			if ($arData['AVAILABLE_QUANTITY_LIST'][$k] > $arStoreData['QUANTITY']) {
				$arData['AVAILABLE_QUANTITY_LIST'][$k] = $arStoreData['QUANTITY'];
			}
		}
		$oResult->setData($arData);
		return $oResult;
	}

	protected function getStoreIds()
	{
		$arResult = $GLOBALS['KIT_MULTIREGIONS']['SYS_STORES'];
		if (!is_array($arResult)) {
			$arResult = array($arResult);
		}

		return $arResult;
	}

	public function getProductListStores(array $products)
	{
		$result = new Result();

		//without store control stores are used for information purposes only
		if (!State::isUsedInventoryManagement())
			return $result;

		$resultList = array();

		$storeIds = $this->getStoreIds();
		if (empty($storeIds))
			return $result;

		$productGetIdList = array();
		foreach ($products as $productId => $productData) {
			$cacheId = md5($productId);

			$storeProductDataList = static::getHitCache(self::CACHE_STORE_PRODUCT, $cacheId);
			if (!empty($storeProductDataList)) {
				$resultList[$productId] = $storeProductDataList;
			} else {
				$productGetIdList[$productId] = $productId;
			}

		}

		if (!empty($productGetIdList)) {
			$iterator = StoreProductTable::getList(array(
				'select' => array('PRODUCT_ID', 'AMOUNT', 'STORE_ID', 'STORE_NAME' => 'STORE.TITLE', 'ID'),
				'filter' => array('=PRODUCT_ID' => $productGetIdList, '@STORE_ID' => $storeIds),
				'order' => array('STORE_ID' => 'ASC'),
			));
			while ($row = $iterator->fetch()) {
				$resultList[$row['PRODUCT_ID']][$row['STORE_ID']] = $row;
			}

			foreach ($productGetIdList as $productId) {
				if (!empty($resultList[$productId])) {
					$cacheId = md5($productId);
					static::setHitCache(self::CACHE_STORE_PRODUCT, $cacheId, $resultList[$productId]);
				}
			}
		}

		if (!empty($resultList)) {
			$result->setData(
				array(
					'PRODUCT_STORES_LIST' => $resultList,
				)
			);
		}
		return $result;
	}
}
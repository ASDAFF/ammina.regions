<?

namespace Ammina\Regions;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Service\GeoIp;
use Bitrix\Main\Service\GeoIp\Result;
use Bitrix\Sale\Location\LocationTable;

Loc::loadMessages(__FILE__);

final class GeoIPHandler extends GeoIp\Base
{

	/**
	 * @return string Title of handler.
	 */
	public function getTitle()
	{
		return Loc::getMessage("AMMINA_REGIONS_GEOIP_HANDLER_TITLE");
	}

	/**
	 * @return string Handler description.
	 */
	public function getDescription()
	{
		return Loc::getMessage("AMMINA_REGIONS_GEOIP_HANDLER_DESCRIPTION");
	}

	public function getSupportedLanguages()
	{
		return array('ru', 'en');
	}

	/**
	 * @return ProvidingData Geolocation information witch handler can return.
	 */
	public function getProvidingData()
	{
		$result = new GeoIp\ProvidingData();
		$result->countryName = true;
		$result->countryCode = true;
		$result->regionName = true;
		$result->regionCode = true;
		$result->cityName = true;
		$result->latitude = true;
		$result->longitude = true;
		$result->timezone = true;
		return $result;
	}

	/**
	 * @param string $ip Ip address
	 * @param string $lang Language identifier
	 *
	 * @return Result | null
	 */
	public function getDataResult($ip, $lang = '')
	{
		$dataResult = new Result();
		$geoData = new GeoIp\Data();
		$geoData->ip = $ip;
		$geoData->lang = $lang = amreg_strlen($lang) > 0 ? $lang : 'ru';

		$app = \Bitrix\Main\Application::getInstance();
		$preventCity = intval($app->getContext()->getRequest()->getCookie("ARG_CITY"));

		$arData = \Ammina\Regions\BlockTable::getCityByIP($ip, $preventCity);
		if (is_array($arData) && $arData['CITY']['ID'] > 0) {
			if (amreg_strpos($arData['CITY']['NAME'], '(') !== false) {
				$arData['CITY']['NAME'] = trim(amreg_substr($arData['CITY']['NAME'], 0, amreg_strpos($arData['CITY']['NAME'], '(') - 1));
			}
			$geoData->countryName = $arData['COUNTRY']['NAME'];
			$geoData->countryCode = $arData['COUNTRY']['CODE'];
			$geoData->regionName = $arData['REGION']['NAME'];
			$geoData->regionCode = $arData['REGION']['CODE'];
			$geoData->cityName = $arData['CITY']['NAME'];
			$geoData->latitude = $arData['CITY']['LAT'];
			$geoData->longitude = $arData['CITY']['LON'];
			$geoData->timezone = $arData['REGION']['TIMEZONE'];
		} else {
			$dataResult->addErrors(array());
		}

		$dataResult->setGeoData($geoData);
		return $dataResult;
	}
}
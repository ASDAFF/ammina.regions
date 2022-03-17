<?

namespace Kit\MultiRegions\Parser;

use Bitrix\Main\Web\HttpClient;

class SypexGeo extends Base
{
	const SXGEO_FILE = 0;
	const SXGEO_MEMORY = 1;
	const SXGEO_BATCH = 2;

	protected $strUrlFileCity = "https://kitgeo.ru/files/SxGeoCity_utf8.zip";
	protected $strUrlFileInfo = "https://kitgeo.ru/files/SxGeo_Info.zip";
	protected $strUrlFileCity2 = "https://sypexgeo.net/files/SxGeoCity_utf8.zip";
	protected $strUrlFileInfo2 = "https://sypexgeo.net/files/SxGeo_Info.zip";
	protected $strUrlFileCity3 = "http://sypexgeo.net/files/SxGeoCity_utf8.zip";
	protected $strUrlFileInfo3 = "http://sypexgeo.net/files/SxGeo_Info.zip";
	protected $id2iso = array(
		'',
		'AP',
		'EU',
		'AD',
		'AE',
		'AF',
		'AG',
		'AI',
		'AL',
		'AM',
		'CW',
		'AO',
		'AQ',
		'AR',
		'AS',
		'AT',
		'AU',
		'AW',
		'AZ',
		'BA',
		'BB',
		'BD',
		'BE',
		'BF',
		'BG',
		'BH',
		'BI',
		'BJ',
		'BM',
		'BN',
		'BO',
		'BR',
		'BS',
		'BT',
		'BV',
		'BW',
		'BY',
		'BZ',
		'CA',
		'CC',
		'CD',
		'CF',
		'CG',
		'CH',
		'CI',
		'CK',
		'CL',
		'CM',
		'CN',
		'CO',
		'CR',
		'CU',
		'CV',
		'CX',
		'CY',
		'CZ',
		'DE',
		'DJ',
		'DK',
		'DM',
		'DO',
		'DZ',
		'EC',
		'EE',
		'EG',
		'EH',
		'ER',
		'ES',
		'ET',
		'FI',
		'FJ',
		'FK',
		'FM',
		'FO',
		'FR',
		'SX',
		'GA',
		'GB',
		'GD',
		'GE',
		'GF',
		'GH',
		'GI',
		'GL',
		'GM',
		'GN',
		'GP',
		'GQ',
		'GR',
		'GS',
		'GT',
		'GU',
		'GW',
		'GY',
		'HK',
		'HM',
		'HN',
		'HR',
		'HT',
		'HU',
		'ID',
		'IE',
		'IL',
		'IN',
		'IO',
		'IQ',
		'IR',
		'IS',
		'IT',
		'JM',
		'JO',
		'JP',
		'KE',
		'KG',
		'KH',
		'KI',
		'KM',
		'KN',
		'KP',
		'KR',
		'KW',
		'KY',
		'KZ',
		'LA',
		'LB',
		'LC',
		'LI',
		'LK',
		'LR',
		'LS',
		'LT',
		'LU',
		'LV',
		'LY',
		'MA',
		'MC',
		'MD',
		'MG',
		'MH',
		'MK',
		'ML',
		'MM',
		'MN',
		'MO',
		'MP',
		'MQ',
		'MR',
		'MS',
		'MT',
		'MU',
		'MV',
		'MW',
		'MX',
		'MY',
		'MZ',
		'NA',
		'NC',
		'NE',
		'NF',
		'NG',
		'NI',
		'NL',
		'NO',
		'NP',
		'NR',
		'NU',
		'NZ',
		'OM',
		'PA',
		'PE',
		'PF',
		'PG',
		'PH',
		'PK',
		'PL',
		'PM',
		'PN',
		'PR',
		'PS',
		'PT',
		'PW',
		'PY',
		'QA',
		'RE',
		'RO',
		'RU',
		'RW',
		'SA',
		'SB',
		'SC',
		'SD',
		'SE',
		'SG',
		'SH',
		'SI',
		'SJ',
		'SK',
		'SL',
		'SM',
		'SN',
		'SO',
		'SR',
		'ST',
		'SV',
		'SY',
		'SZ',
		'TC',
		'TD',
		'TF',
		'TG',
		'TH',
		'TJ',
		'TK',
		'TM',
		'TN',
		'TO',
		'TL',
		'TR',
		'TT',
		'TV',
		'TW',
		'TZ',
		'UA',
		'UG',
		'UM',
		'US',
		'UY',
		'UZ',
		'VA',
		'VC',
		'VE',
		'VG',
		'VI',
		'VN',
		'VU',
		'WF',
		'WS',
		'YE',
		'YT',
		'RS',
		'ZA',
		'ZM',
		'ME',
		'ZW',
		'A1',
		'XK',
		'O1',
		'AX',
		'GG',
		'IM',
		'JE',
		'BL',
		'MF',
		'BQ',
		'SS',
	);
	protected $range, $b_idx_len, $m_idx_len, $db_items, $id_len, $block_len, $max_region, $max_city, $max_country, $country_size, $batch_mode, $memory_mode, $pack, $b_idx_str,
		$m_idx_str, $db_begin, $b_idx_arr, $db, $multiregions_db, $cities_db, $info, $ip1c;
	protected $fCityFile = null;

	protected function setDefaultOptions()
	{
		$this->strDataLocalDir = $_SERVER['DOCUMENT_ROOT'] . "/bitrix/kit/multiregions/db/sypexgeo/";
		CheckDirPath($this->strDataLocalDir);
		CheckDirPath($this->strDataLocalDir . "info/");
		CheckDirPath($this->strDataLocalDir . "city/");
	}

	public function doDownloadFileInfo()
	{
		$bResult = true;
		$client = new HttpClient(
			array(
				'redirect' => true,
				'redirectMax' => 10,
				'socketTimeout' => 300,
				'streamTimeout' => 300,
				'disableSslVerification' => true
			)
		);
		if (!$client->download($this->strUrlFileInfo, $this->strDataLocalDir . "info.zip")) {
			if (!$client->download($this->strUrlFileInfo2, $this->strDataLocalDir . "info.zip")) {
				if (!$client->download($this->strUrlFileInfo3, $this->strDataLocalDir . "info.zip")) {
					return false;
				}
			}
		}
		$status = intval($client->getStatus());
		if ($status != 200 && file_exists($this->strDataLocalDir . "info.zip")) {
			@unlink($this->strDataLocalDir . "info.zip");
			return false;
		}
		return $bResult;
	}

	public function doDownloadFileCity()
	{
		$bResult = true;
		$client = new HttpClient(
			array(
				'redirect' => true,
				'redirectMax' => 10,
				'socketTimeout' => 300,
				'streamTimeout' => 300,
				'disableSslVerification' => true
			)
		);
		if (!$client->download($this->strUrlFileCity, $this->strDataLocalDir . "city.zip")) {
			if (!$client->download($this->strUrlFileCity2, $this->strDataLocalDir . "city.zip")) {
				if (!$client->download($this->strUrlFileCity3, $this->strDataLocalDir . "city.zip")) {
					return false;
				}
			}
		}
		$status = intval($client->getStatus());
		if ($status != 200 && file_exists($this->strDataLocalDir . "city.zip")) {
			@unlink($this->strDataLocalDir . "city.zip");
			return false;
		}
		return $bResult;
	}

	public function doExtractFileInfo()
	{
		$bResult = true;
		$oArchive = \CBXArchive::GetArchive($this->strDataLocalDir . "info.zip");
		$oArchive->SetOptions(
			array(
				"REMOVE_PATH" => $_SERVER["DOCUMENT_ROOT"],
				"UNPACK_REPLACE" => true,
			)
		);
		$uRes = $oArchive->Unpack($this->strDataLocalDir . "info/");
		if (!$uRes) {
			$bResult = false;
		}
		unset($oArchive);
		return $bResult;
	}

	public function doExtractFileCity()
	{
		$bResult = true;
		$oArchive = \CBXArchive::GetArchive($this->strDataLocalDir . "city.zip");
		$oArchive->SetOptions(
			array(
				"REMOVE_PATH" => $_SERVER["DOCUMENT_ROOT"],
				"UNPACK_REPLACE" => true,
			)
		);
		$uRes = $oArchive->Unpack($this->strDataLocalDir . "city/");
		if (!$uRes) {
			$bResult = false;
		}
		unset($oArchive);
		return $bResult;
	}

	public function loadCityFile($type = SypexGeo::SXGEO_FILE)
	{
		$bResult = true;
		if (!file_exists($this->strDataLocalDir . "city/SxGeoCity.dat")) {
			return false;
		}
		$this->fCityFile = fopen($this->strDataLocalDir . "city/SxGeoCity.dat", "rb");
		if (!$this->fCityFile) {
			return false;
		}
		if ($this->parseCityFileHeaders($type)) {
		} else {
			$bResult = false;
		}
		return $bResult;
	}

	public function closeCityFile()
	{
		fclose($this->fCityFile);
	}

	protected function parseCityFileHeaders($type = SypexGeo::SXGEO_FILE)
	{
		$header = fread($this->fCityFile, 40);
		if (substr($header, 0, 3) != 'SxG') {
			return false;
		}
		$info = unpack('C3chars/Cver/Ntime/Ctype/Ccharset/Cb_idx_len/nm_idx_len/nrange/Ndb_items/Cid_len/nmax_region/nmax_city/Nregion_size/Ncity_size/nmax_country/Ncountry_size/npack_size', $header);
		if ($info['b_idx_len'] * $info['m_idx_len'] * $info['range'] * $info['db_items'] * $info['time'] * $info['id_len'] == 0) {
			return false;
		}
		$this->range = $info['range'];
		$this->b_idx_len = $info['b_idx_len'];
		$this->m_idx_len = $info['m_idx_len'];
		$this->db_items = $info['db_items'];
		$this->id_len = $info['id_len'];
		$this->block_len = 3 + $this->id_len;
		$this->max_region = $info['max_region'];
		$this->max_city = $info['max_city'];
		$this->max_country = $info['max_country'];
		$this->country_size = $info['country_size'];
		$this->batch_mode = $type & SypexGeo::SXGEO_BATCH;
		$this->memory_mode = $type & SypexGeo::SXGEO_MEMORY;
		$this->pack = $info['pack_size'] ? explode("\0", fread($this->fCityFile, $info['pack_size'])) : '';
		$this->b_idx_str = fread($this->fCityFile, $info['b_idx_len'] * 4);
		$this->m_idx_str = fread($this->fCityFile, $info['m_idx_len'] * 4);

		$this->db_begin = ftell($this->fCityFile);
		if ($this->batch_mode) {
			$this->b_idx_arr = array_values(unpack("N*", $this->b_idx_str));
			unset ($this->b_idx_str);
			$this->m_idx_arr = str_split($this->m_idx_str, 4);
			unset ($this->m_idx_str);
		}
		if ($this->memory_mode) {
			$this->db = fread($this->fCityFile, $this->db_items * $this->block_len);
			$this->multiregions_db = $info['region_size'] > 0 ? fread($this->fCityFile, $info['region_size']) : '';
			$this->cities_db = $info['city_size'] > 0 ? fread($this->fCityFile, $info['city_size']) : '';
		}
		$this->info = $info;
		$this->info['multiregions_begin'] = $this->db_begin + $this->db_items * $this->block_len;
		$this->info['cities_begin'] = $this->info['multiregions_begin'] + $info['region_size'];
	}

	protected function search_idx($ipn, $min, $max)
	{
		if ($this->batch_mode) {
			while ($max - $min > 8) {
				$offset = ($min + $max) >> 1;
				if ($ipn > $this->m_idx_arr[$offset]) {
					$min = $offset;
				} else {
					$max = $offset;
				}
			}
			while ($ipn > $this->m_idx_arr[$min] && $min++ < $max) {
			};
		} else {
			while ($max - $min > 8) {
				$offset = ($min + $max) >> 1;
				if ($ipn > substr($this->m_idx_str, $offset * 4, 4)) {
					$min = $offset;
				} else {
					$max = $offset;
				}
			}
			while ($ipn > substr($this->m_idx_str, $min * 4, 4) && $min++ < $max) {
			};
		}
		return $min;
	}

	protected function search_db($str, $ipn, $min, $max)
	{
		if ($max - $min > 1) {
			$ipn = substr($ipn, 1);
			while ($max - $min > 8) {
				$offset = ($min + $max) >> 1;
				if ($ipn > substr($str, $offset * $this->block_len, 3)) {
					$min = $offset;
				} else {
					$max = $offset;
				}
			}
			while ($ipn >= substr($str, $min * $this->block_len, 3) && ++$min < $max) {
			};
		} else {
			$min++;
		}
		return hexdec(bin2hex(substr($str, $min * $this->block_len - $this->id_len, $this->id_len)));
	}

	protected function get_num($ip)
	{
		$ip1n = (int)$ip; // ������ ����
		if ($ip1n == 0 || $ip1n == 10 || $ip1n == 127 || $ip1n >= $this->b_idx_len || false === ($ipn = ip2long($ip))) {
			return false;
		}
		$ipn = pack('N', $ipn);
		$this->ip1c = chr($ip1n);
		// ������� ���� ������ � ������� ������ ����
		if ($this->batch_mode) {
			$blocks = array('min' => $this->b_idx_arr[$ip1n - 1], 'max' => $this->b_idx_arr[$ip1n]);
		} else {
			$blocks = unpack("Nmin/Nmax", substr($this->b_idx_str, ($ip1n - 1) * 4, 8));
		}
		if ($blocks['max'] - $blocks['min'] > $this->range) {
			// ���� ���� � �������� �������
			$part = $this->search_idx($ipn, floor($blocks['min'] / $this->range), floor($blocks['max'] / $this->range) - 1);
			// ����� ����� ����� � ������� ����� ������ IP, ������ ������� ������ ���� � ��
			$min = $part > 0 ? $part * $this->range : 0;
			$max = $part > $this->m_idx_len ? $this->db_items : ($part + 1) * $this->range;
			// ����� ��������� ����� ���� �� ������� �� ������� ����� ������� �����
			if ($min < $blocks['min']) {
				$min = $blocks['min'];
			}
			if ($max > $blocks['max']) {
				$max = $blocks['max'];
			}
		} else {
			$min = $blocks['min'];
			$max = $blocks['max'];
		}
		$len = $max - $min;
		// ������� ������ �������� � ��
		if ($this->memory_mode) {
			return $this->search_db($this->db, $ipn, $min, $max);
		} else {
			fseek($this->fCityFile, $this->db_begin + $min * $this->block_len);
			return $this->search_db(fread($this->fCityFile, $len * $this->block_len), $ipn, 0, $len);
		}
	}

	protected function readData($seek, $max, $type)
	{
		$raw = '';
		if ($seek && $max) {
			if ($this->memory_mode) {
				$raw = substr($type == 1 ? $this->multiregions_db : $this->cities_db, $seek, $max);
			} else {
				fseek($this->fCityFile, $this->info[$type == 1 ? 'multiregions_begin' : 'cities_begin'] + $seek);
				$raw = fread($this->fCityFile, $max);
			}
		}
		return $this->unpack($this->pack[$type], $raw);
	}

	protected function parseCity($seek, $full = false)
	{
		if (!$this->pack) {
			return false;
		}
		$only_country = false;
		if ($seek < $this->country_size) {
			$country = $this->readData($seek, $this->max_country, 0);
			$city = $this->unpack($this->pack[2]);
			$city['lat'] = $country['lat'];
			$city['lon'] = $country['lon'];
			$only_country = true;
		} else {
			$city = $this->readData($seek, $this->max_city, 2);
			$country = array('id' => $city['country_id'], 'iso' => $this->id2iso[$city['country_id']]);
			unset($city['country_id']);
		}
		if ($full) {
			$region = $this->readData($city['region_seek'], $this->max_region, 1);
			if (!$only_country) {
				$country = $this->readData($region['country_seek'], $this->max_country, 0);
			}
			if (!defined("BX_UTF") || BX_UTF !== true) {
				if (isset($country) && !empty($country) && $country['id'] > 0) {
					$country['name_ru'] = iconv("utf-8", "windows-1251", $country['name_ru']);
					$country['name_en'] = iconv("utf-8", "windows-1251", $country['name_en']);
				}
				if (isset($region) && !empty($region) && $region['id'] > 0) {
					$region['name_ru'] = iconv("utf-8", "windows-1251", $region['name_ru']);
					$region['name_en'] = iconv("utf-8", "windows-1251", $region['name_en']);
				}
				if (isset($city) && !empty($city) && $city['id'] > 0) {
					$city['name_ru'] = iconv("utf-8", "windows-1251", $city['name_ru']);
					$city['name_en'] = iconv("utf-8", "windows-1251", $city['name_en']);
				}
			}
			unset($city['region_seek']);
			unset($region['country_seek']);
			return array('city' => $city, 'region' => $region, 'country' => $country);
		} else {
			if (!defined("BX_UTF") || BX_UTF !== true) {
				if (isset($country) && !empty($country) && $country['id'] > 0) {
					$country['name_ru'] = iconv("utf-8", "windows-1251", $country['name_ru']);
					$country['name_en'] = iconv("utf-8", "windows-1251", $country['name_en']);
				}
				if (isset($city) && !empty($city) && $city['id'] > 0) {
					$city['name_ru'] = iconv("utf-8", "windows-1251", $city['name_ru']);
					$city['name_en'] = iconv("utf-8", "windows-1251", $city['name_en']);
				}
			}
			unset($city['region_seek']);
			return array('city' => $city, 'country' => array('id' => $country['id'], 'iso' => $country['iso']));
		}
	}

	protected function unpack($pack, $item = '')
	{
		$unpacked = array();
		$empty = empty($item);
		$pack = explode('/', $pack);
		$pos = 0;
		foreach ($pack as $p) {
			list($type, $name) = explode(':', $p);
			$type0 = $type{0};
			if ($empty) {
				$unpacked[$name] = $type0 == 'b' || $type0 == 'c' ? '' : 0;
				continue;
			}
			switch ($type0) {
				case 't':
				case 'T':
					$l = 1;
					break;
				case 's':
				case 'n':
				case 'S':
					$l = 2;
					break;
				case 'm':
				case 'M':
					$l = 3;
					break;
				case 'd':
					$l = 8;
					break;
				case 'c':
					$l = (int)substr($type, 1);
					break;
				case 'b':
					$l = strpos($item, "\0", $pos) - $pos;
					break;
				default:
					$l = 4;
			}
			$val = substr($item, $pos, $l);
			switch ($type0) {
				case 't':
					$v = unpack('c', $val);
					break;
				case 'T':
					$v = unpack('C', $val);
					break;
				case 's':
					$v = unpack('s', $val);
					break;
				case 'S':
					$v = unpack('S', $val);
					break;
				case 'm':
					$v = unpack('l', $val . (ord($val{2}) >> 7 ? "\xff" : "\0"));
					break;
				case 'M':
					$v = unpack('L', $val . "\0");
					break;
				case 'i':
					$v = unpack('l', $val);
					break;
				case 'I':
					$v = unpack('L', $val);
					break;
				case 'f':
					$v = unpack('f', $val);
					break;
				case 'd':
					$v = unpack('d', $val);
					break;

				case 'n':
					$v = current(unpack('s', $val)) / pow(10, $type{1});
					break;
				case 'N':
					$v = current(unpack('l', $val)) / pow(10, $type{1});
					break;

				case 'c':
					$v = rtrim($val, ' ');
					break;
				case 'b':
					$v = $val;
					$l++;
					break;
			}
			$pos += $l;
			$unpacked[$name] = is_array($v) ? current($v) : $v;
		}
		return $unpacked;
	}

	public function get($ip)
	{
		return $this->max_city ? $this->getCity($ip) : $this->getCountry($ip);
	}

	public function getCountry($ip)
	{
		if (defined("BX_UTF") && BX_UTF === true) {
			mb_internal_encoding("cp-1251");
		}
		if ($this->max_city) {
			$tmp = $this->parseCity($this->get_num($ip));
			$result = $tmp['country']['iso'];
		} else {
			$result = $this->id2iso[$this->get_num($ip)];
		}
		if (defined("BX_UTF") && BX_UTF === true) {
			mb_internal_encoding("utf-8");
		}
		return $result;
	}

	public function getCountryId($ip)
	{
		if (defined("BX_UTF") && BX_UTF === true) {
			mb_internal_encoding("cp-1251");
		}
		if ($this->max_city) {
			$tmp = $this->parseCity($this->get_num($ip));
			$result = $tmp['country']['id'];
		} else {
			$result = $this->get_num($ip);
		}
		if (defined("BX_UTF") && BX_UTF === true) {
			mb_internal_encoding("utf-8");
		}
		return $result;
	}

	public function getCity($ip)
	{
		if (defined("BX_UTF") && BX_UTF === true) {
			mb_internal_encoding("cp-1251");
		}
		$seek = $this->get_num($ip);
		$result = ($seek ? $this->parseCity($seek) : false);
		if (defined("BX_UTF") && BX_UTF === true) {
			mb_internal_encoding("utf-8");
		}
		return $result;
	}

	public function getCityFull($ip)
	{
		if (defined("BX_UTF") && BX_UTF === true) {
			mb_internal_encoding("cp-1251");
		}
		$seek = $this->get_num($ip);
		$result = ($seek ? $this->parseCity($seek, 1) : false);
		if (defined("BX_UTF") && BX_UTF === true) {
			mb_internal_encoding("utf-8");
		}
		return $result;
	}

	public function about()
	{
		$charset = array('utf-8', 'latin1', 'cp1251');
		$types = array('n/a', 'SxGeo Country', 'SxGeo City RU', 'SxGeo City EN', 'SxGeo City', 'SxGeo City Max RU', 'SxGeo City Max EN', 'SxGeo City Max');
		return array(
			'Created' => date('Y.m.d', $this->info['time']),
			'Timestamp' => $this->info['time'],
			'Charset' => $charset[$this->info['charset']],
			'Type' => $types[$this->info['type']],
			'Byte Index' => $this->b_idx_len,
			'Main Index' => $this->m_idx_len,
			'Blocks In Index Item' => $this->range,
			'IP Blocks' => $this->db_items,
			'Block Size' => $this->block_len,
			'City' => array(
				'Max Length' => $this->max_city,
				'Total Size' => $this->info['city_size'],
			),
			'Region' => array(
				'Max Length' => $this->max_region,
				'Total Size' => $this->info['region_size'],
			),
			'Country' => array(
				'Max Length' => $this->max_country,
				'Total Size' => $this->info['country_size'],
			),
		);
	}

	public function doLoadExternalData()
	{
		if (!$this->doDownloadFileInfo()) {
			return false;
		}
		if (!$this->doExtractFileInfo()) {
			return false;
		}
		if (!$this->doDownloadFileCity()) {
			return false;
		}
		if (!$this->doExtractFileCity()) {
			return false;
		}
	}
}
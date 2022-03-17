<?

namespace Kit\MultiRegions\Parser;

abstract class Base
{
	public $strDataLocalDir = "";

	public function __construct()
	{
		$this->setDefaultOptions();
	}

	abstract protected function setDefaultOptions();

	abstract public function doLoadExternalData();

}
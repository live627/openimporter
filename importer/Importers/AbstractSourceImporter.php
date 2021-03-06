<?php
/**
 * @name      OpenImporter
 * @copyright OpenImporter contributors
 * @license   BSD http://opensource.org/licenses/BSD-3-Clause
 *
 * @version 2.0 Alpha
 */

namespace OpenImporter\Importers;

/**
 * Class AbstractSourceImporter
 * This abstract class is the base for any php importer file.
 *
 * It provides some common necessary methods and some default properties
 * so that Importer can do its job without having to test for existence
 * of methods every two/three lines of code.
 *
 * @package OpenImporter\Importers
 */
abstract class AbstractSourceImporter implements SourceImporterInterface
{
	protected $setting_file = '';

	protected $path = '';

	protected $db = null;

	protected $config = null;

	protected $fields = array();

	public function setUtils($db, $config)
	{
		$this->db = $db;
		$this->config = $config;
	}

	public function setField($key, $val)
	{
		$this->fields[$key] = $val;
	}

	public function getAllFields()
	{
		return $this->fields;
	}

	public function getField($key)
	{
		if (isset($this->fields[$key]))
		{
			return $this->fields[$key];
		}
		else
		{
			return null;
		}
	}

	abstract public function getName();

	abstract public function getVersion();

	abstract public function getDbPrefix();

	abstract public function getDbName();

	abstract public function dbConnectionData();

	public function loadSettings($path, $test = false)
	{
		if ($test)
		{
			if (empty($this->setting_file))
			{
				return null;
			}

			return $this->testPath($path);
		}

		if (empty($this->setting_file))
		{
			return true;
		}

		if ($this->testPath($path))
		{
			// Error silenced in case the settings file defines constants and related "Constant already defined"
			@include($path . $this->setting_file);

			return true;
		}
		else
		{
			return false;
		}
	}

	protected function testPath($path)
	{
		$found = file_exists($path . $this->setting_file);

		if ($found)
		{
			$this->path = $path;
		}

		return $found;
	}

	public function setDefines()
	{
	}

	public function setGlobals()
	{
	}

	public function callMethod($method, $params = null)
	{
		if (method_exists($this, $method))
		{
			return call_user_func_array(array($this, $method), array($params));
		}
		else
		{
			return $params;
		}
	}

	abstract protected function getTableTest();

	protected function readSettingsFile()
	{
		static $content = null;

		if ($content === null)
		{
			$content = file_get_contents($this->path . $this->setting_file);
		}

		return $content;
	}
}
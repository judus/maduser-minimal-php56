<?php namespace Maduser\Minimal\Http;

/**
 * Class Request
 *
 * @package Maduser\Minimal\Http
 */
class Request implements RequestInterface
{
	/**
     * The current uri string
     *
	 * @var
	 */
	private $uriString;

	/**
     * The current http method
     *
	 * @var
	 */
	private $requestMethod;

	/**
     * Holds all the uri segments until ? or #
     *
	 * @var array
	 */
	private $segments = [];

	/**
     * Setter $uriString
     *
	 * @param $str
	 */
	public function setUriString($str)
	{
		$this->uriString = $str;
	}

	/**
     * Getter $uriString
     *
	 * @return string
	 */
	public function getUriString()
	{
		return $this->uriString;
	}

	/**
     * Setter $requestMethod
     *
	 * @param $str
	 */
	public function setRequestMethod($str)
	{
		$this->requestMethod = $str;
	}

	/**
     * Getter $requestMethod
     *
	 * @return mixed
	 */
	public function getRequestMethod()
	{
		return $this->requestMethod;
	}

	/**
     * Setter $segments
     *
	 * @param array $segments
	 */
	public function setSegments(array $segments)
	{
		$this->segments = $segments;
	}

	/**
     * Getter $segments
     *
	 * @return array
	 */
	public function getSegments()
	{
		return $this->segments;
	}

	/**
     * Request constructor
     * sets $requestMethod
     * sets $uriString
     * sets $segments
	 */
	public function __construct()
	{
		$this->fetchRequestMethod();
		$this->fetchUriString();
		$this->explodeSegments();
	}

	/**
	 * Fetch the http method
	 */
	public function fetchRequestMethod()
	{
        if (php_sapi_name() == 'cli' or defined('STDIN')) {
            $this->setRequestMethod('CLI');
            return;
        }

        if (isset($_POST['_method'])) {
			if (
				strtoupper($_POST['_method']) == 'PUT' ||
                strtoupper($_POST['_method']) == 'PATCH' ||
                strtoupper($_POST['_method']) == 'DELETE'
			) {
				$this->setRequestMethod(strtoupper($_POST['_method']));
				return;
			}
		}

		$this->setRequestMethod($_SERVER['REQUEST_METHOD']);
	}

	/**
	 * Fetches the REQUEST_URI and sets $uriString
	 */
	public function fetchUriString()
	{
		if (php_sapi_name() == 'cli' or defined('STDIN')) {
			$this->setUriString($this->parseCliArgs());
			return;
		}

        // Fetch request string (apache)
		$uri = $_SERVER['REQUEST_URI'];
        $uri = parse_url($uri)['path'];

        // Further cleaning of the uri
		$uri = str_replace(array('//', '../'), '/', trim($uri, '/'));

		if (empty($uri)) {
			$uri = '/';
		}


        $this->setUriString($uri);
	}

	/**
     * Formats cli args like a uri
     *
	 * @return string
	 */
	public function parseCliArgs()
	{
		$args = array_slice($_SERVER['argv'], 1);

		return $args ? '/' . implode('/', $args) : '';
	}

	/**
     * Filter or replace bad chars from uri
     *
	 * @param $uri
	 *
	 * @return mixed
	 */
	public function filterUri($uri)
	{
		$bad = array('$', '(', ')', '%28', '%29');
		$good = array('&#36;', '&#40;', '&#41;', '&#40;', '&#41;');

		return str_replace($bad, $good, $uri);
	}

	/**
	 * Explodes the uri string
	 */
	public function explodeSegments()
	{
		foreach (
			explode("/", preg_replace("|/*(.+?)/*$|", "\\1", $this->uriString))
			as $val
		) {
			$val = trim($this->filterUri($val));

			if ($val != '') {
				$this->segments[] = $val;
			}
		}
	}

	public function segment($n)
    {
        if (isset($this->getSegments()[$n])) {
            return $this->getSegments()[$n];
        }

        return null;
    }
}
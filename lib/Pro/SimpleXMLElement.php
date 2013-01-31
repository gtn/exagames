<?php
/**
 * Pro Framework
 *
 * @category   Pro
 * @author     Pro-Web (http://pro-web.at)
 * @copyright  Copyright (c) 2008 pro-web.at (http://pro-web.at)
 * @version    $Id: SimpleXMLElement.php 182 2009-01-29 10:19:58Z danielpr $
 */

class Pro_SimpleXMLElement extends SimpleXMLElement
{
	public static function createRoot($rootElement)
	{
		return self::create($rootElement);
	}

	public static function create($rootElement)
	{
		return new self('<?xml version="1.0" encoding="UTF-8"?><'.$rootElement.' />');
	}

	public function addAttribute($name="", $value="", $namespace = null)
	{
		parent::addAttribute($name, $value, $namespace = null);

		return $this;
	}

	public function setAttribute($name, $value)
	{
		$this[$name] = $value;

		return $this;
	}
	
	public function setAttributes($data)
	{
		foreach ($data as $key => $value) {
			$this[$key] = $value;
		}
		
		return $this;
	}

	public function addCData($cdata_text)
	{
		$node = dom_import_simplexml($this);
		$node->appendChild($node->ownerDocument->createCDATASection($cdata_text));

		return $this;
	}

	public function addChild($name, $value = null, $namespace = null)
	{
		if ($name instanceof SimpleXMLElement) {
			$newNode = $name;
			$node = dom_import_simplexml($this);
			$newNode = $node->ownerDocument->importNode(dom_import_simplexml($newNode), true);
			$node->appendChild($newNode);

			// return last children, this is the added child!
			$children = $this->children();
			return $children[count($children)-1];
		} else {
			return parent::addChild($name, $value, $namespace);
		}
	}

	public function setValue($value)
	{
		$this[0] = $value;

		return $this;
	}

	/**
     * Outputs this element as pretty XML to increase readability.
     * @param   int     $spaces     (optional) The number of spaces to use for
     *                              indentation, defaults to 4
     * @return  string              The XML output
     * @access  public
     */
    public function asPrettyXML($spaces = 4)
    {
        // get an array containing each XML element
        $xml = explode("\n", preg_replace('/>\s*</', ">\n<", $this->asXML()));

        // hold current indentation level
        $indent = 0;

        // hold the XML segments
        $pretty = array();

        // shift off opening XML tag if present
        if (count($xml) && preg_match('/^<\?\s*xml/', $xml[0])) {
            $pretty[] = array_shift($xml);
        }

        $lastOpened = false;
		foreach ($xml as $el) {
            if (preg_match('/^<([\w])+[^>\/]*>$/U', $el)) {
                // opening tag, increase indent
                $pretty[] = str_repeat(' ', $indent) . $el;
                $indent += $spaces;
				$lastOpened = true;
            } else {
                if ($closing = preg_match('/^<\/.+>$/', $el)) {
                    // closing tag, decrease indent
                    $indent -= $spaces;
                }
				if ($closing && $lastOpened) {
					$pretty[count($pretty)-1] .= $el;
				} else {
	                $pretty[] = str_repeat(' ', $indent) . $el;
				}
				$lastOpened = false;
            }
        }

        return implode("\n", $pretty);
    }
    
    /**
     * Returns a string representation of the object
     * Alias for toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
		return $this->asXML();
	}
}

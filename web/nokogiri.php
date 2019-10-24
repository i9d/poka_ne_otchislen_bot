<?php

/*
 * This file is part of the zero package.
 * Copyright (c) 2012 olamedia <olamedia@gmail.com>
 *
 * This source code is release under the MIT License.
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Simple HTML parser
 *
 * @author olamedia <olamedia@gmail.com>
 */


class nokogiri implements IteratorAggregate{
	const
	regexp = 
	"/(?P<tag>[a-z0-9]+)?(\[(?P<attr>\S+)(=(?P<value>[^\]]+))?\])?(#(?P<id>[^\s:>#\.]+))?(\.(?P<class>[^\s:>#\.]+))?(:(?P<pseudo>(first|last|nth)-child)(\((?P<expr>[^\)]+)\))?)?\s*(?P<rel>>)?/isS"
	;
	protected $_source = '';
	/**
	 * @var DOMDocument
	 */
	protected $_dom = null;
	/**
	 * @var DOMDocument
	 */
	protected $_tempDom = null;
	/**
	 * @var DOMXpath
	 * */
	protected $_xpath = null;
	/**
 	 * @var libxmlErrors
 	 */
	protected $_libxmlErrors = null;
	protected static $_compiledXpath = array();
	public function __construct($htmlString = ''){
		$this->loadHtml($htmlString);
	}
	public function getRegexp(){
		$tag = "(?P<tag>[a-z0-9]+)?";
		$attr = "(\[(?P<attr>\S+)=(?P<value>[^\]]+)\])?";
		$id = "(#(?P<id>[^\s:>#\.]+))?";
		$class = "(\.(?P<class>[^\s:>#\.]+))?";
		$child = "(first|last|nth)-child";
		$expr = "(\((?P<expr>[^\)]+)\))";
		$pseudo = "(:(?P<pseudo>".$child.")".$expr."?)?";
		$rel = "\s*(?P<rel>>)?";
		$regexp = "/".$tag.$attr.$id.$class.$pseudo.$rel."/isS";
		return $regexp;
	}
	public static function fromHtml($htmlString){
		$me = new self();
		$me->loadHtml($htmlString);
		return $me;
	}
	public static function fromHtmlNoCharset($htmlString){
		$me = new self();
		$me->loadHtmlNoCharset($htmlString);
		return $me;
	}
	public static function fromDom($dom){
		$me = new self();
		$me->loadDom($dom);
		return $me;
	}
	public function loadDom($dom){
		$this->_dom = $dom;
	}
	public function loadHtmlNoCharset($htmlString = ''){
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->preserveWhiteSpace = false;
		if (strlen($htmlString)){
			libxml_use_internal_errors(true);
			$this->_libxmlErrors = null;
			$dom->loadHTML('<?xml encoding="UTF-8">'.$htmlString);
			// dirty fix
			foreach ($dom->childNodes as $item){
			    if ($item->nodeType == XML_PI_NODE){
			        $dom->removeChild($item); // remove hack
			        break;
			    }
			}
			$dom->encoding = 'UTF-8'; // insert proper
			$this->_libxmlErrors = libxml_get_errors();
			libxml_clear_errors();
		}
		$this->loadDom($dom);
	}
	public function loadHtml($htmlString = ''){
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->preserveWhiteSpace = false;
		if (strlen($htmlString)){
			libxml_use_internal_errors(true);
			$this->_libxmlErrors = null;
			$dom->loadHTML($htmlString);
			$this->_libxmlErrors = libxml_get_errors();
			libxml_clear_errors();
		}
		$this->loadDom($dom);
	}
	public function getErrors(){
 		return $this->_libxmlErrors;
 	}
	public function __invoke($expression){
		return $this->get($expression);
	}
	public function get($expression, $compile = true){
		return $this->getElements($this->getXpathSubquery($expression, false, $compile));
	}
	protected function getNodes(){

	}
	public function getDom($asIs = false){
		if ($asIs){
			return $this->_dom;
		}
		if ($this->_dom instanceof DOMDocument){
			return $this->_dom;
		}elseif ($this->_dom instanceof DOMNodeList || $this->_dom instanceof DOMElement){
			if ($this->_tempDom === null){
				$this->_tempDom = new DOMDocument('1.0', 'UTF-8');
				$root = $this->_tempDom->createElement('root');
				$this->_tempDom->appendChild($root);
				if($this->_dom instanceof DOMNodeList){
					foreach ($this->_dom as $domElement){
						$domNode = $this->_tempDom->importNode($domElement, true);
						$root->appendChild($domNode);
					}
				}else{
					$domNode = $this->_tempDom->importNode($this->_dom, true);
					$root->appendChild($domNode);
				}
			}
			return $this->_tempDom;
		}
	}
	protected function getXpath(){
		if ($this->_xpath === null){
			$this->_xpath = new DOMXpath($this->getDom());
		}
		return $this->_xpath;
	}
	public function getXpathSubquery($expression, $rel = false, $compile = true){
		if ($compile){
			$key = $expression.($rel?'>':'*');
			if (isset(self::$_compiledXpath[$key])){
				return self::$_compiledXpath[$key];
			}
		}
		$query = '';
		if (preg_match(self::regexp, $expression, $subs)){
			$brackets = array();
			if (isset($subs['id']) && '' !== $subs['id']){
				$brackets[] = "@id='".$subs['id']."'";
			}
			if (isset($subs['attr']) && '' !== $subs['attr']){
				if (!(isset($subs['value']))) {
					$brackets[] = "@".$subs['attr'];
				} else {
					$attrValue = !empty($subs['value'])?$subs['value']:'';
					$brackets[] = "@".$subs['attr']."='".$attrValue."'";
				}
			}
			if (isset($subs['class']) && '' !== $subs['class']){
				$brackets[] = 'contains(concat(" ", normalize-space(@class), " "), " '.$subs['class'].' ")';
			}
			if (isset($subs['pseudo']) && '' !== $subs['pseudo']){
				if ('first-child' === $subs['pseudo']){
					$brackets[] = '1';
				}elseif ('last-child' === $subs['pseudo']){
					$brackets[] = 'last()';
				}elseif ('nth-child' === $subs['pseudo']){
					if (isset($subs['expr']) && '' !== $subs['expr']){
						$e = $subs['expr'];
						if('odd' === $e){
							$brackets[] = '(position() -1) mod 2 = 0 and position() >= 1';
						}elseif('even' === $e){
							$brackets[] = 'position() mod 2 = 0 and position() >= 0';
						}elseif(preg_match("/^[0-9]+$/", $e)){
							$brackets[] = 'position() = '.$e;
						}elseif(preg_match("/^((?P<mul>[0-9]+)n\+)(?P<pos>[0-9]+)$/is", $e, $esubs)){
							if (isset($esubs['mul'])){
								$brackets[] = '(position() -'.$esubs['pos'].') mod '.$esubs['mul'].' = 0 and position() >= '.$esubs['pos'].'';
							}else{
								$brackets[] = ''.$e.'';
							}
						}
					}
				}
			}
			$query = ($rel?'/':'//').
				((isset($subs['tag']) && '' !== $subs['tag'])?$subs['tag']:'*').
				(($c = count($brackets))?
					($c>1?'[('.implode(') and (', $brackets).')]':'['.implode(' and ', $brackets).']')
				:'')
				;
			$left = trim(substr($expression, strlen($subs[0])));
			if ('' !== $left){
				$query .= $this->getXpathSubquery($left, isset($subs['rel'])?'>'===$subs['rel']:false, $compile);
			}
		}
		if ($compile){
			self::$_compiledXpath[$key] = $query;
		}
		return $query;
	}
	protected function getElements($xpathQuery){
		if (strlen($xpathQuery)){
			$nodeList = $this->getXpath()->query($xpathQuery);
			if ($nodeList === false){
				throw new Exception('Malformed xpath');
			}
			return self::fromDom($nodeList);
		}
	}
	public function toDom($asIs = false){
		return $this->getDom($asIs);
	}
	public function toXml(){
		return $this->getDom()->saveXML();
	}
	public function toArray($xnode = null){
		$array = array();
		if ($xnode === null){
			if ($this->_dom instanceof DOMNodeList){
				foreach ($this->_dom as $node){
					$array[] = $this->toArray($node);
				}
				return $array;
			}
			$node = $this->getDom();
		}else{
			$node = $xnode;
		}
		if (in_array($node->nodeType, array(XML_TEXT_NODE,XML_COMMENT_NODE))){
			return $node->nodeValue;
		}
		if ($node->hasAttributes()){
			foreach ($node->attributes as $attr){
				$array[$attr->nodeName] = $attr->nodeValue;
			}
		}
		if ($node->hasChildNodes()){
			foreach ($node->childNodes as $childNode){
				$array[$childNode->nodeName][] = $this->toArray($childNode);
			}
		}
		if ($xnode === null){
			$a = reset($array);
			return reset($a); // first child
		}
		return $array;
	}
	public function getIterator(){
		$a = $this->toArray();
		return new ArrayIterator($a);
	}
	protected function _toTextArray($node = null, $skipChildren = false, $singleLevel = true){
		$array = array();
		if ($node === null){
			$node = $this->getDom();
		}
		if ($node instanceof DOMNodeList){
			foreach ($node as $child){
				if ($singleLevel){
					$array = array_merge($array, $this->_toTextArray($child, $skipChildren, $singleLevel));
				}else{
					$array[] = $this->_toTextArray($child, $skipChildren, $singleLevel);
				}
			}
			return $array;
		}
		if (XML_TEXT_NODE === $node->nodeType){
			return array($node->nodeValue);
		}
		if (!$skipChildren){
			if ($node->hasChildNodes()){
				foreach ($node->childNodes as $childNode){
					if ($singleLevel){
						$array = array_merge($array, $this->_toTextArray($childNode, $skipChildren, $singleLevel));
					}else{
						$array[] = $this->_toTextArray($childNode, $skipChildren, $singleLevel);
					}
				}
			}
		}
		return $array;
	}
	public function toTextArray($skipChildren = false, $singleLevel = true){
		return $this->_toTextArray($this->_dom, $skipChildren, $singleLevel);
	}
	public function toText($glue = ' ', $skipChildren = false){
		return implode($glue, $this->toTextArray($skipChildren, true));
	}
	
}
//*[@id="content"]/div/div/article/div/font/table/tbody/tr[5]/td[2]
function cell($tab, $x, $y)
{
	if(is_array($tab->toArray()[$x]['td'][$y]["#text"]))
	{
		$content_count = count($tab->toArray()[$x]['td'][$y]["#text"]);
	}
	else
	{
		$content_count = 0;
	}
	if ($content_count>1)
	{
	for ($i = 0; $i < $content_count; $i++)
	{
		$str .= $tab->toArray()[$x]['td'][$y]["#text"][$i]; //+ '\n';
		//$str .= '\n';
	}
	return $str;
	}
	return $tab->toArray()[$x]['td'][$y]["#text"][0];
}

function colspan($tab, $x, $y)
{
	$span = 0;
//	$size = count($tab->toArray()[$x]['td']);
	for ($i = 0; $i < $y; $i++)
	{
		if ($tab->toArray()[$x]['td'][$i]["colspan"][0] > 0)
		{
			$span++;
		}
	}
	return $span;
}


	$day = 'ПН';
	$group = 3;
	
	$day_arr = array('ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ');
	$indexOfDayArr = array_search($day, $day_arr);  
	$html = file_get_contents('http://fkn.univer.omsk.su/academics/Schedule/schedule3_1.htm');
	$saw = new nokogiri($html);
	$table = $saw->get('table'); 
	$tr = $table->get('tr');
	
	$str = 0;		//строка
	$t = 1; 	//время
	
//	var_dump( $tr->toArray()[$str]['td'][$group]["#text"] );
//	echo count( $tr->toArray()[$str]['td'][$group]["#text"] );
	/*__________________________________________*/ // Ищем нужный день (индекс строки, где нужный день)
	while (cell($tr, $str, 0) !== $day)
	{$str++;}
	
	/*__________________________________________*/ //Парсим группу, выводим группу и день
	$egroup = cell($tr, 0, $group);
	echo ($egroup);
	echo ("\n");
	echo ($day);
	echo ("\n");
	/*__________________________________________*/

	$time = cell($tr, $str, $t);	//парсим время
	$span = colspan($tr, $str, $group);
	$lesson = cell($tr, $str, $group-$span);	//парсим пару
	if($lesson !== Null)	//если  пара есть, выводим время и пару
	{
		echo ($time);
		echo ("\n");
		echo ($lesson);
		echo ("\n");
	}

	$group--;	//сдвиг влево из-за того, что был день недели
	$t--;
	$str++;		//следующая строка
	
	while (cell($tr, $str, 0) !== $day_arr[$indexOfDayArr+1])
	{
		$time = cell($tr, $str, $t);
		$span = colspan($tr, $str, $group);
		$lesson = cell($tr, $str, $group-$span);
		if($lesson !== Null)
		{
			echo ($time);
			echo ("\n");
			echo ($lesson);
			echo ("\n");
		}
		$str++;
	} 

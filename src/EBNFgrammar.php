<?php
	/**------------------------------------------------------------------------------
	 * Title:        EBNF Parser - Grammar generator
	 * Filename:     EBNFgrammar.php
	 * Version:      0.1
	 * Author:       Adrian M. Partl, Richard Keizer (evolved from pragmatic-parser.php to this)
	 * Email:        adrian at partl dot net
	 *-------------------------------------------------------------------------------
	 * COPYRIGHT (c) 2014 Adrian M. Partl
	 * pragmatic-parser.php (https://code.google.com/p/pragmatic-parser/):
	 * COPYRIGHT (c) 2011 Richard Keizer
	 *
	 * The source code included in this package is free software; you can
	 * redistribute it and/or modify it under the terms of the GNU General Public
	 * License as published by the Free Software Foundation. This license can be
	 * read at:
	 *
	 * http://www.opensource.org/licenses/gpl-license.php
	 *
	 * This program is distributed in the hope that it will be useful, but WITHOUT
	 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
	 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
	 *------------------------------------------------------------------------------
	 *
	 * taking BNF parser pragmatic-parser.php and turning it into EBNF (with some refactoring)
	 *
	 * retaining the pragmatic approach though ;-)
	 */	

	namespace EBNFparser;

	class Grammar {
		protected $rules = array();
		
		public function __construct($text) {
			$this->parseRules($text);
		}
		
		protected function parseRules($text) {
			$this->rules = array();
			foreach (preg_split("/(\n|\r|\r\n)/", $text) as $rule) {
				$rule = trim($rule);

				if (!empty($rule)) {
					$rule = new Rule($rule);
					$this->rules[$rule->getName()][] = $rule;
				}
			}
		}
		
		public function getRulesByName($ruleName) {
			if(!array_key_exists($ruleName, $this->rules)) {
				throw new \Exception("Unknown rule: " . $ruleName);
			}

			return $this->rules[$ruleName];
		}
	}

	class Rule {
		protected $name;
		protected $symbols = array();
		
		public function __construct($rule, $subRule = false) {
			if($subRule === false) {
				$sides = preg_split("/\s*(::=|=)\s*/smi", $rule);
				//dirty hack since I'm too stupid to use regexes (is this actually possible at all?)
				if(count($sides) > 2) {
					$tmp[0] = $sides[0];
					unset($sides[0]);
					$tmp[1] = substr($rule, strpos($rule, "=") + 1);
					$sides = $tmp;
				}

				$this->name = $sides[0];
				$this->symbols = $this->splitRHSintoSymbols($sides[1]);
			} else {
				$this->name = "SUBRULE";
				$this->symbols = $this->splitRHSintoSymbols($rule);
			}
		}
		
		protected function tokenizeRHS($text) {
			$gather = false;
			$alterGather = false;
			$phrase = false;
			$alterPhrase = false;
			$splitArray = array();

			$cursor = 0;

			$tokens = preg_split("/[\s]+/smi", trim($text));
			$numTokens = count($tokens);

			$result = $this->tokenizeRecursively($tokens, $cursor);

			return $result;
		}

		protected function tokenizeRecursively($tokens, &$cursor, $encounteredAlternation=False, $encounteredBracket=False) {
			$numTokens = count($tokens);
			$result = array();

			while($cursor < $numTokens) {
				$symbol = $tokens[$cursor];

				if($symbol == '[' || $symbol == '(' || $symbol == '{') {
					$cursor += 1;
					$tmp = array();
					$tmp[] = $symbol;
					$tmp = array_merge($tmp, $this->tokenizeRecursively($tokens, $cursor, $encounteredAlternation, True));
					$result[] = trim($this->implode_r(" ", $tmp));
					continue;
				}

				if($symbol == ']' || $symbol == ')' || $symbol == '}') {
					$cursor += 1;
					$result[] = $symbol;
					return $result;
				}

				if($symbol == '|' && $encounteredBracket === False) {
					//Alternations can only occur, if there is something else before them
					//Since this is the first time an alternation occurs at this recursion stage
					//there can only be ONE entry in result
					if(count($result) !== 1) {
						throw new \Exception("Error in grammar, something is wrong with your alternation");
					}

					$cursor += 1;
					$alternations = $this->tokenizeRecursively($tokens, $cursor, True);
					if($encounteredAlternation === True) {
						$result = array_merge($result, $alternations);
					} else {
						$tmp = array_merge($result, $alternations);
						array_pop($result);
						$result[] = $tmp;
					}

					return $result;
				}

				if($symbol[0] == 'R' && $symbol[1] == 'E' && $symbol[2] == '<') {
					$result[] = $this->tokenizeRegex($tokens, $cursor);
					$cursor += 1;
					continue;
				}

				$result[] = $symbol;
				$cursor += 1;
			}

			return $result;
		}

		protected function implode_r($glue, array $array) {
			$result = "";

			foreach($array as $element) {
				if(is_array($element)) {
					$result .= $glue . $this->implode_r($glue, $element);
				} else {
					$result .= $glue . $element;
				}
			}

			return $result;
		}

		protected function tokenizeRegex($tokens, &$cursor) {
			$numTokens = count($tokens);
			$result = array();

			while($cursor < $numTokens) {
				$symbol = $tokens[$cursor];

				if($symbol[strlen($symbol) - 1] != '>') {
					$result[] = $symbol;
					$cursor += 1;
				} else {
					$result[] = $symbol;
					return implode(" ", $result);
				}
			}
		}

		protected function splitRHSintoSymbols($text) {
			$result = array();

			$splitArray = $this->tokenizeRHS($text);

			foreach ($splitArray as $symbol) {
				if($symbol[0] == '[') {
					$result[] = new Option($symbol);
				} else if ($symbol[0] == '{') {
					$result[] = new Repetition($symbol);
				} else if ($symbol[0] == '(') {
					$result[] = new Group($symbol);
				} else if ($symbol[0] == "'" || $symbol[0] == '"') {
					$result[] = new TermString($symbol);
				} else if (is_array($symbol)) {
					//only alternations come out as arrays from the tokeniser
					$result[] = new Alternation($symbol);
				} else if($symbol[0] == 'R' && $symbol[1] == 'E' && $symbol[2] == '<') {
					$result[] = new Regex($symbol);
				} else {
					//if we arrived here, the symbol is a terminal rule
					$result[] = new TermRule($symbol);
				}
			}

			return $result;
		}
		
		public function getName() {
			return $this->name;
		}
		
		public function getSymbols() {
			return $this->symbols;
		}
	}
	
	abstract class Symbol {
		protected $type = "UNKNOWN";
		protected $value;

		public function __construct($value) {
			if(is_array($value)) {
				throw new \Exception("Unknown Symbol definition - Array given");
			}

			$this->value = trim($value);
		}

		public function getValue() {
			return $this->value;
		}

		public function getType() {
			return $this->type;
		}
	}	

	class TermString extends Symbol {
		protected $type = "TERMSTRING";

		public function __construct($value) {
			$value = trim($value);

			if($value[0] == "'") {
				$this->value = trim($value, "'");
			} else if ($value[0] == '"') {
				$this->value = trim($value, '"');
			}
		}
	}

	class TermRule extends Symbol {
		protected $type = "TERMRULE";
	}

	class Regex extends Symbol {
		protected $type = "REGEX";

		public function __construct($value) {
			//handle regexes
			if(strpos($value, "RE<") === 0) {
				$this->value = trim(substr($value, 3), ">");
			} else {
				$this->value = $value;
			}
		}
	}

	class Group extends Symbol {
		protected $type = "GROUP";

		public function __construct($value) {
			$value = trim($value);

			//handle groups
			if($value[0] == '(') {
				$value = preg_replace("/^\(?(.*?)\)?$/", '$1', $value);
			}

			$this->value = new Rule($value, true);
		}
	}

	class Option extends Symbol {
		protected $type = "OPTION";

		public function __construct($value) {
			$value = trim($value);

			//handle options
			if($value[0] == '[') {
				$value = preg_replace("/^\[?(.*?)\]?$/", '$1', $value);
			}

			$this->value = new Rule($value, true);
		}
	}

	class Repetition extends Symbol {
		protected $type = "REPETITION";

		public function __construct($value) {
			$value = trim($value);

			//handle repetition
			if($value[0] == '{') {
				$value = preg_replace("/^\{?(.*?)\}?$/", '$1', $value);
			}

			$this->value = new Rule($value, true);
		}
	}

	class Alternation extends Symbol {
		protected $type = "ALTERNATION";

		public function __construct($values) {
			//alternations have to be arrays
			if(!is_array($values)) {
				throw new \Exception("Alternations need to be arrays, something else given.");
			}

			$this->value = array();

			foreach($values as $value) {
				if($value != '|') {
					$this->value[] = new Rule($value, true);
				}
			}
		}
	}
	

<?php
	/**------------------------------------------------------------------------------
	 * Title:        EBNF Parser - Parse tree generator
	 * Filename:     EBNFparser.php
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

  	error_reporting(E_ALL);
  	include_once 'EBNFgrammar.php';

  	use \DOMDocument;

	class Parser {
		protected $grammar;
		protected $lexer;
		protected $parsetree;

		public function __construct(Grammar $grammar, Lexer $lexer) {
			$this->grammar = $grammar;
			$this->lexer = $lexer;
		}
		
		public function setText($text) {
			$this->lexer->setText($text);
		}

		public function createParsetree() {
			$this->parsetree = array();
			
			if ($result = $this->matchRuleByName()) $this->parsetree = $result;

			return $this->parsetree;
		}
		
		protected function matchRuleByName($lhs='root', &$cursor=0) {
			$startcursor = $cursor;

			$result = array();
			
			$rules = $this->grammar->getRulesByName($lhs);

			foreach ($rules as $rule) {
				$match = $this->matchRule($rule, $cursor);

				if(!empty($match)) {
					$result = $match;
					break;
				} else {
					$cursor = $startcursor;
				}
			}

			return $result;
		}

		protected function matchRule($rule=false, &$cursor=0) {
			$result = array();

			//anything left to do?
			if($cursor >= $this->lexer->getLength()) {
				return $result;
			}
			
			foreach ($rule->getSymbols() as $symbol) {
				$match = $this->matchSymbol($symbol, $cursor);
				
				if (empty($match) && $symbol->getType() === "OPTION") {
					continue;
				} else if (empty($match)) {
					return array();
					break;
				}

				if($symbol->getType() === "TERMRULE") {
					if(array_key_exists($symbol->getValue(), $result)) {
						//determine a possible array key
						$count = 1;
						while(array_key_exists($symbol->getValue() . "_" . $count, $result)) {
							$count += 1;
						}
						$result[$symbol->getValue() . "_" . $count] = $match;
					} else {
						$result[$symbol->getValue()] = $match;
					}
				} else {
					//some cleaning work to get a nicer representation at the end
					if(!is_array($match)) {
						if(array_key_exists($rule->getName(), $result)) {
							//determine a possible array key
							$count = 1;
							while(array_key_exists($rule->getName() . "_" . $count, $result)) {
								$count += 1;
							}
							$result[$rule->getName() . "_" . $count] = $match;
						} else {
							$result[$rule->getName()] = $match;
						}
						continue;
					} else {
						$result[] = $match;
					}
				}
			}

			//clean things up
			if(count($result) === 1) {
				$result = array_pop($result);
			}
			
			return $result;
		}

		protected function matchSymbol($symbol=false, &$cursor) {
			switch ($symbol->getType()) {
				case 'OPTION':
					//get the subrule and process
					$subrule = $symbol->getValue();

					return $this->matchRule($subrule, $cursor);
					break;
				
				case 'TERMRULE':
					//check the referenced rule
					return $this->matchRuleByName($symbol->getValue(), $cursor);
					break;

				case 'TERMSTRING':
					//check string
					if($symbol->getValue() === $this->lexer->getToken($cursor)) {
						$token = $this->lexer->getToken($cursor);
						$cursor += 1;
						return $token;
					}
					break;

				case 'REGEX':
					//check regex
					if(preg_match("/{$symbol->getValue()}/sm", $this->lexer->getToken($cursor))) {
						$token = $this->lexer->getToken($cursor);
						$cursor += 1;
						return $token;
					}
					break;

				case 'ALTERNATION':
					//go through the list of possible rules and take the first one that evaluates
					foreach($symbol->getValue() as $alterRule) {
						$match = $this->matchRule($alterRule, $cursor);

						if(!empty($match)) {
							return $match;
						}
					}
					break;

				case 'REPETITION':
					$end = false;
					$match = array();
					while($end === false) {
						$currMatch = false;

						$currMatch = $this->matchRule($symbol->getValue(), $cursor);

						if(empty($currMatch)) {
							$end = true;
							break;
						}

						$match[] = $currMatch;
					}
					return $match;
					break;

				case 'GROUP':
					//get the subrule and process
					$subrule = $symbol->getValue();

					$match = $this->matchRule($subrule, $cursor);

					if(empty($match)) {
						return array();
					} else {
						return array($subrule->getName() => $match);
					}
					break;

				default:
					throw new \Exception("Unimplemented " . $symbol->getType());

					break;
			}

			return array();
		}

		protected function createNode($name, $value, $subtree=False) {
			$node = array();

			$node['expr_type'] = $name;
			$node['base_name'] = $value;
			$node['subtree'] = $subtree;

			return $node;
		}
	}

	class parseException extends \Exception {
		protected $symbol;
		protected $token;

		public function __construct($message="", $symbol, $token) {
			parent::__construct($message);
			$this->symbol = $symbol;
			$this->token = $token;
		}

		public function getErrorMessage() {
			return "Parse error: " . parent::getMessage() . " occured at position " . $token . " for rule with name " . $symbol->getName();
		}
	}

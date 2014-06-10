<?php
	/**------------------------------------------------------------------------------
	 * Title:        EBNF Parser - Lexer abstract class
	 * Filename:     EBNFlexer.php
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

	abstract class Lexer {
		protected $tokens = array();
		protected $length = 0;
		
		public function __construct($input) {
			$this->setText($input);
		}
		
		public function tokenCount() {
			return count($this->tokens);
		}
		
		public function getToken($i) {
			return array_key_exists($i, $this->tokens) ? $this->tokens[$i] : false;
		}
		
		public function setText($text) {
			$this->tokens = $this->tokenize($text);
			$this->length = count($this->tokens);
		}

		public function getLength() {
			return $this->length;
		}

		abstract protected function tokenize($text);
	}
	

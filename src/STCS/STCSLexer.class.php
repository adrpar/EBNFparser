<?php
  /**------------------------------------------------------------------------------
   * Title:        SCTS Grammar lexer
   * Filename:     STCSLexer.class.php
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
   */
  
  include_once 'EBNFlexer.php';

  class STCSLexer extends EBNFparser\Lexer {
    protected function tokenize($text) {
      $result = preg_split("/[\s]+/", $text);

      return $result;
    }
  }
  

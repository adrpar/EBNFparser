<?php
  /**------------------------------------------------------------------------------
   * Title:        Usage example of the Pragmatic EBNF-a-like parser
   * Filename:     example_rtf.php
   * Version:      0.2
   * Author:       Richard Keizer
   * Email:        ra dot keizer at gmail dot com
   *-------------------------------------------------------------------------------
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
   *
   */
  
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  
  include_once 'EBNFparser.php';
  include_once 'RTF/RTFLexer.class.php';
  
  use EBNFparser\Parser;
  use EBNFparser\Grammar;

  $parser = new Parser(
                       new Grammar(file_get_contents('RTF/rtf.grammar.txt')),
                       new RTFLexer(file_get_contents('RTF/simple.rtf'))
                       );
  var_dump($parser->createParsetree());


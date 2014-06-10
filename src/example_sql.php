<?php
  /**------------------------------------------------------------------------------
   * Title:        Usage example of the Pragmatic EBNF-a-like parser
   * Filename:     example_sql.php
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
   * the SQL grammar used herein is just a small subset of the
   * real deal, it just serves as an example how to write a grammar file.
   *
   * note    Parts of the regex used by the tokenizer come from an unknown source.
   *         I rewrote it to fit my needs, I hope I don't violate anyones license.
   *         pls contact me to get proper credits!
   *
   */
  
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  
  include_once 'EBNFparser.php';
  include_once 'SQL/SQLLexer.class.php';
  
  use EBNFparser\Parser;
  use EBNFparser\Grammar;
  
  $parser = new Parser(
                       new Grammar(file_get_contents('SQL/sql.grammar.txt')),
                       new SQLLexer('SELECT a,b,c FROM BOOKSTORE.authors WHERE (age<150) OR (lastname IN ("Verne", "Dickens"))')
                       );
  var_dump($parser->createParsetree());



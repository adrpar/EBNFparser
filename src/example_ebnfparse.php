<?php
  /**------------------------------------------------------------------------------
   * Title:        Usage example of the Pragmatic EBNF-a-like parser
   * Filename:     example_ebnf.php
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
  include_once 'EBNFParse/EBNFParseLexer.class.php';
 
  use EBNFparser\Parser;
  use EBNFparser\Grammar;

  $fileContent = file_get_contents('STCS/stcs.grammar.txt');

  $parser = new Parser(
                       new Grammar(file_get_contents('EBNFParse/ebnfparse.grammar.txt')),
                       new EBNFParseLexer('')
                       );

  foreach(preg_split("/(\n|\r|\r\n)/", $fileContent) as $line) {
    var_dump($line);
    $parser->setText($line);
    var_dump($parser->createParsetree());
  }




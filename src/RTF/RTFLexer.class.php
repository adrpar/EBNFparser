<?php
  /**------------------------------------------------------------------------------
   * Title:        RTF Lexer
   * Filename:     RTFLexer.class.php
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
   */

  include_once 'EBNFlexer.php';

  class RTFLexer extends EBNFparser\Lexer {
    protected function tokenize($text) {
      $r = '[{}]';
      
      $r .= '|\\\\\*';
      $r .= '|\\\[A-Za-z]+(?:-?\d+)?(?:[^{}\\\\]*)?';
      $r .= '|\\\\\'[0-9a-f]{2}+[\s]?';
      
      $r .= '|[^{}\\\\]+';  //text!
      
      preg_match_all('/' . $r . '/sm', $text, $result);

      return $result[0];
    }
  }
  

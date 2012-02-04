<?php
/**
 * Note : Code is released under the GNU LGPL
 *
 * Please do not change the header of this file
 *
 * This library is free software; you can redistribute it and/or modify it under the terms of the GNU
 * Lesser General Public License as published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * See the GNU Lesser General Public License for more details.
 */

/**
 * Parser
 *
 * AcceptHeader := AcceptItem ["," AcceptItem]
 * AcceptItem   := TERM "/" TERM [ AcceptParam ]
 * AcceptParams := ";" TERM "=" TERM [ AcceptParam ]
 *
 * @author      Pierrick Charron <pierrick@webstart.fr>
 */
class AcceptParser {

    /**
     * List of parser
     */
    const T_SLASH      = 256,
          T_SEMICOLUMN = 257,
          T_COMA       = 258,
          T_EQUAL      = 259,
          T_TERM       = 260;

    /**
     * Lexer
     * @var AcceptLexer
     */
    private $lexer = null;

    /** 
     * Constructor
     * 
     * @param AcceptLexer Lexer
     * @return void
     */
    public function __construct($lexer)
    {
        $this->lexer = $lexer;
    }

    /**
     * Parse the Accept header
     *
     * @return array
     */
    public function parse()
    {
        $items = array();

        $this->lexer->yylex();
        do {
            $items[] = $this->_parseAcceptItem();
            $continue = false;
            if ($this->lexer->token) {

                if ($this->lexer->token === self::T_COMA) {
                    $this->lexer->yylex();
                    $continue = true;
                } else {
                    $this->error();
                }
            }
        } while ($continue);

        return $items;
    }

    private function _parseAcceptItem()
    {
        $item = array();
        if ($this->lexer->token === self::T_TERM) {
            $item['type'] = $this->lexer->value;
            $this->lexer->yylex();
        } else {
            $this->error();
        }

        if ($this->lexer->token === self::T_SLASH) {
            $this->lexer->yylex();
        } else {
            $this->error();
        }

        if ($this->lexer->token === self::T_TERM) {
            $item['subtype'] = $this->lexer->value;
            $this->lexer->yylex();
        } else {
            $this->error();
        }

        $item['params'] = array();
        while ($this->lexer->token === self::T_SEMICOLUMN) {
            list($name, $value) = $this->_parseAcceptItemParams();
            $item['params'][$name] = $value;
        }
        return $item;
    }

    private function _parseAcceptItemParams()
    {
        $param = array();
        $this->lexer->yylex();
        
        if ($this->lexer->token === self::T_TERM) {
            $param[] = $this->lexer->value;
            $this->lexer->yylex();
        } else {
            $this->error();
        }

        if ($this->lexer->token === self::T_EQUAL) {
            $this->lexer->yylex();
        } else {
            $this->error();
        }
        
        if ($this->lexer->token === self::T_TERM) {
            $param[] = $this->lexer->value;
            $this->lexer->yylex();
        } else {
            $this->error();
        }

        return $param;
    }

    public function error()
    {
        throw new \Exception('Parse error : \'' . $this->lexer->value . '\' ( position: ' . $this->lexer->count . ')');
    }
}

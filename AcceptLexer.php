<?php
/**
 * Lexer for an accept header
 *
 * @author      Pierrick Charron <pierrick@webstart.fr>
 */
class AcceptLexer {

    /**
     * List of tokens
     * @var array
     */
    private $tokenMap = array(
        '/'           => AcceptParser::T_SLASH,
        ';'           => AcceptParser::T_SEMICOLUMN,
        ','           => AcceptParser::T_COMA,
        '='           => AcceptParser::T_EQUAL,
        '\s+'         => false,
        '[^/;,=\s]*'  => AcceptParser::T_TERM
    );

    /**
     * Regex to match the tokens
     * @var string
     */
    protected $regex;

    /**
     * Input data
     * @var string
     */
    protected $data;

    /**
     * Char position
     * @var int
     */
    public $count;

    /**
     * Token
     * @var int
     */
    public $token;

    /**
     * Value
     * @var mixed
     */
    public $value;

    /**
     * Lexer constructor
     * 
     * @param string $data Data to lex
     * @return void
     */
    public function __construct($data) {
        $this->regex = '#\G(' . implode(')|\G(', array_keys($this->tokenMap)) . ')#A';
        $this->tokenMap = array_values($this->tokenMap);
        $this->count = 0;
        $this->data = $data;
    }

    /**
     * Get the next lexem
     * 
     * @return boolean true if a lexem was found, false otherwise
     */
    public function yylex() {
        $tokens = array();
        while (isset($this->data[$this->count])) {
            if (!preg_match($this->regex, $this->data, $matches, null, $this->count)) {
                throw new LexingException(sprintf('Unexpected character "%s"', $this->data[$this->count]));
            }
            for ($i = 1; '' === $matches[$i]; ++$i);
            if ($this->tokenMap[$i - 1]) {
                $this->token = $this->tokenMap[$i - 1];
                $this->value = $matches[0];
                $this->count += strlen($matches[0]);
                return true;
            } else {
                $this->count += strlen($matches[0]);
            }
        }
        $this->token = $this->value = null;
        return false;
    }
}

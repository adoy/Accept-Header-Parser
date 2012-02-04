<?php

/**
 * The AcceptHeader page will parse and sort the different
 * allowed types for the content negociations
 */
class AcceptHeader extends \ArrayObject {

    /**
     * Constructor
     *
     * @param string $header Value of the Accept header
     * @return void
     */
    public function __construct($header)
    {
        $al = new AcceptLexer($header);
        $ap = new AcceptParser($al);
        $acceptedTypes = $ap->parse();
        usort($acceptedTypes, array($this, '_compare'));
        parent::__construct($acceptedTypes);
    }

    /**
     * Parse the accept header and return an array containing
     * all the informations about the Accepted types
     *
     * @param string $header Value of the Accept header
     * @return array
     */
    private function _parse($data)
    {
        $array = array();
        $items = explode(',', $data);
        foreach ($items as $item) {
            $elems = explode(';', $item);

            $acceptElement = array();
            list($type, $subtype) = explode('/', current($elems));
            $acceptElement['type'] = trim($type);
            $acceptElement['subtype'] = trim($subtype);


            $acceptElement['params'] = array();
            while(next($elems)) {
                list($name, $value) = explode('=', current($elems));
                $acceptElement['params'][trim($name)] = trim($value);
            }

            $array[] = $acceptElement;

        }
        return $array;
    }

    /**
     * Compare two Accepted types with their parameters to know
     * if one media type should be used instead of an other
     *
     * @param array $a The first media type and its parameters
     * @param array $b The second media type and its parameters
     * @return int
     */
    private function _compare($a, $b) {
        $a_q = isset($a['params']['q']) ? floatval($a['params']['q']) : 1.0;
        $b_q = isset($b['params']['q']) ? floatval($b['params']['q']) : 1.0;
        if ($a_q === $b_q) {
            $a_count = count($a['params']);
            $b_count = count($b['params']);
            if ($a_count === $b_count) {
                if ($r = $this->_compareSubType($a['subtype'], $b['subtype'])) {
                    return $r;
                } else {
                    return $this->_compareSubType($a['type'], $b['type']);
                }
            } else {
                return $a_count < $b_count;
            }
        } else {
            return $a_q < $b_q;
        }
    }

    /**
     * Compare two subtypes
     *
     * @param string $a First subtype to compare
     * @param string $b Second subtype to compare
     * @return int
     */
    private function _compareSubType($a, $b) 
    {
        if ($a === '*' && $b !== '*') {
            return 1;
        } elseif ($b === '*' && $a !== '*') {
            return -1;
        } else {
            return 0;
        }
    }

}

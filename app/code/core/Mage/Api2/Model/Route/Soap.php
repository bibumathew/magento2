<?php

class Mage_Api2_Model_Route_Soap implements Mage_Api2_Model_Route_Interface
{
    /**
     * Matches a Request with parts defined by a map. Assigns and
     * returns an array of variables on a successful match.
     *
     * @param Mage_Api2_Model_Request $request
     * @return array|false An array of assigned values or a false on a mismatch
     */
    public function match($request)
    {
        $values = array();

        return $values;
    }
}

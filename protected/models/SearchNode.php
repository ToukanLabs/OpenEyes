<?php

/**
 * Class SearchNode
 */
abstract class SearchNode extends CFormModel
{
    /**
     * @param $universal_set int[] The universal set of IDs. This is primarily used for NOT nodes.
     * @return int[] A list of IDs.
     */
    abstract public function getResultSet($universal_set);

    /**
     * Render the parameter on-screen.
     * @param $id integer The position of the parameter in the list of parameters.
     */
    abstract public function renderNode($id);
}
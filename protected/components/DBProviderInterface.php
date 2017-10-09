<?php

/**
 * Interface DBProviderInterface
 */
interface DBProviderInterface
{
    /**
     * Generate a SQL fragment representing the subquery of a FROM condition.
     * @param $searchProvider DBProvider The database search provider.
     * @return string The constructed query string.
     */
    public function query($searchProvider);

    /**
     * Get the list of bind values for use in the SQL query.
     * @return array An array of bind values. The keys correspond to the named binds in the query string.
     */
    public function bindValues();

    /**
     * Get the list of joins this parameter needs
     * @return array of string query joins to append onto the main search one for each table
     */
    public function getJoins();

    /**
     * Get the where condition of this parameter
     * @return string The SQL string representing the WHERE clause.
     */
    public function getWhereCondition();
}
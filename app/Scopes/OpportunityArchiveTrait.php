<?php namespace App\Scopes;

trait OpportunityArchiveTrait
{

    /**
     * Boot the scope.
     *
     * @return void
     */
    public static function bootLoadActiveTrait()
    {
        static::addGlobalScope(new OpportunityArchiveScope);
    }

    /**
     * Get the query builder without the scope applied.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function withArchived()
    {
        return with(new static)->newQueryWithoutScope(new OpportunityArchiveScope);
    }

    /**
     * Get the query builder with only the scope applied.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function onlyArchived()
    {
        $instance = new static;

        $column = $instance->getQualifiedPublishedColumn();
        return with(new static)->newQueryWithoutScope(new OpportunityArchiveScope)->where($column, '1');
    }

    /**
     * Get the fully qualified column name for applying the scope.
     *
     * @return string
     */
    public function getQualifiedPublishedColumn()
    {
        return $this->getTable() . '.' . $this->getPublishedColumn();
    }

    /**
     * Get the name of the column for applying the scope.
     *
     * @return string
     */
    public function getPublishedColumn()
    {
        return defined('static::PUBLISHED_COLUMN') ? static::PUBLISHED_COLUMN : 'is_archived';
    }

//    DeleteList
    public static function onlyDeleteLists()
    {
        $instance = new static;

        $column = $instance->getQualifiedPublishedColumnDeleteList();
        return with(new static)->newQueryWithoutScope(new OpportunityArchiveScope)->where($column, '1');
    }

    /**
     * Get the fully qualified column name for applying the scope.
     *
     * @return string
     */
    public function getQualifiedPublishedColumnDeleteList()
    {
        return $this->getTable() . '.' . $this->getPublishedColumnDeleteList();
    }

    /**
     * Get the name of the column for applying the scope.
     *
     * @return string
     */
    public function getPublishedColumnDeleteList()
    {
        return defined('static::PUBLISHED_COLUMN') ? static::PUBLISHED_COLUMN : 'is_delete_list';
    }

    //    Converted list
    public static function onlyConvertedLists()
    {
        $instance = new static;

        $column = $instance->getQualifiedPublishedColumnConvertedList();
        return with(new static)->newQueryWithoutScope(new OpportunityArchiveScope)->where($column, '1');
    }

    /**
     * Get the fully qualified column name for applying the scope.
     *
     * @return string
     */
    public function getQualifiedPublishedColumnConvertedList()
    {
        return $this->getTable() . '.' . $this->getPublishedColumnConvertedList();
    }

    /**
     * Get the name of the column for applying the scope.
     *
     * @return string
     */
    public function getPublishedColumnConvertedList()
    {
        return defined('static::PUBLISHED_COLUMN') ? static::PUBLISHED_COLUMN : 'is_converted_list';
    }
}

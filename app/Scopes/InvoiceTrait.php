<?php namespace App\Scopes;

trait InvoiceTrait
{

    /**
     * Boot the scope.
     *
     * @return void
     */
    public static function bootLoadActiveTrait()
    {
        static::addGlobalScope(new InvoiceScope);
    }

    /**
     * Get the query builder without the scope applied.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function withDeleteList()
    {
        return with(new static)->newQueryWithoutScope(new InvoiceScope);
    }

    /**
     * Get the query builder with only the scope applied.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function onlyDeleteLists()
    {
        $instance = new static;

        $column = $instance->getQualifiedPublishedColumn();
        return with(new static)->newQueryWithoutScope(new InvoiceScope)->where($column, '1');
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
        return defined('static::PUBLISHED_COLUMN') ? static::PUBLISHED_COLUMN : 'is_delete_list';
    }


    //    Converted list
    public static function onlyPaidLists()
    {
        $instance = new static;

        $column = $instance->getQualifiedPublishedColumnPaidList();
        return with(new static)->newQueryWithoutScope(new InvoiceScope)->where($column, 'Paid Invoice');
    }

    /**
     * Get the fully qualified column name for applying the scope.
     *
     * @return string
     */
    public function getQualifiedPublishedColumnPaidList()
    {
        return $this->getTable() . '.' . $this->getPublishedColumnPaidList();
    }

    /**
     * Get the name of the column for applying the scope.
     *
     * @return string
     */
    public function getPublishedColumnPaidList()
    {
        return defined('static::PUBLISHED_COLUMN') ? static::PUBLISHED_COLUMN : 'status';
    }

}

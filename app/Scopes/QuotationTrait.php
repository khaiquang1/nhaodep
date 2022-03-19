<?php namespace App\Scopes;

trait QuotationTrait
{

    /**
     * Boot the scope.
     *
     * @return void
     */
    public static function bootLoadActiveTrait()
    {
        static::addGlobalScope(new QuotationScope);
    }

    /**
     * Get the query builder without the scope applied.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function withDeleteList()
    {
        return with(new static)->newQueryWithoutScope(new QuotationScope);
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
        return with(new static)->newQueryWithoutScope(new QuotationScope)->where($column, '1');
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
    public static function onlyConvertedLists()
    {
        $instance = new static;

        $column = $instance->getQualifiedPublishedColumnConvertedList();
        return with(new static)->newQueryWithoutScope(new QuotationScope)->where($column, '1');
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

    //    Invoice list
    public static function onlyQuotationInvoiceLists()
    {
        $instance = new static;

        $column = $instance->getQualifiedPublishedColumnQuotationInvoiceList();
        return with(new static)->newQueryWithoutScope(new QuotationScope)->where($column, '1');
    }

    /**
     * Get the fully qualified column name for applying the scope.
     *
     * @return string
     */
    public function getQualifiedPublishedColumnQuotationInvoiceList()
    {
        return $this->getTable() . '.' . $this->getPublishedColumnQuotationInvoiceList();
    }

    /**
     * Get the name of the column for applying the scope.
     *
     * @return string
     */
    public function getPublishedColumnQuotationInvoiceList()
    {
        return defined('static::PUBLISHED_COLUMN') ? static::PUBLISHED_COLUMN : 'is_quotation_invoice_list';
    }

    //    Draft quotation
    public static function onlyDraftQuotations()
    {
        $instance = new static;

        $column = $instance->getQualifiedPublishedColumnDraftQuotations();
        return with(new static)->newQueryWithoutScope(new QuotationScope)->where($column, 'Draft Quotation');
    }

    /**
     * Get the fully qualified column name for applying the scope.
     *
     * @return string
     */
    public function getQualifiedPublishedColumnDraftQuotations()
    {
        return $this->getTable() . '.' . $this->getPublishedColumnDraftQuotations();
    }

    /**
     * Get the name of the column for applying the scope.
     *
     * @return string
     */
    public function getPublishedColumnDraftQuotations()
    {
        return defined('static::PUBLISHED_COLUMN') ? static::PUBLISHED_COLUMN : 'status';
    }
}

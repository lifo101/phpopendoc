<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 * 
 */
namespace PHPDOC\Element;

// @codeCoverageIgnoreStart 

/**
 * TableInterface defines the interface for tables.
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss <lifo101@gmail.com>
 */
interface TableInterface
{

    /**
     * Create a new Table instance.
     *
     * This is a shortcut so the caller doesn't have to assign "new" to a
     * variable before they can build out the table.
     * 
     * @param mixed $properties Table level properties
     * @return Table Returns a new Table instance.
     */
    //public static function create($properties = null);
    
    /**
     * Start a new row.
     *
     * This changes the current context of the object to a new row. So any
     * row or cell operations performed from this point onward are applied to
     * the new row.
     *
     * @param mixed $properties Row level properties
     * @return Table Always returns $this
     */
    public function row($properties = null);
    
    /**
     * Add a cell to the current row.
     *
     * @param mixed $elements A single block level element (Paragraph) or an array of elements.
     * @param mixed $properties Cell level properties
     * @return Table Always returns $this
     */
    public function cell($elements = null, $properties = null);
    
    /**
     * Start the grid context.
     *
     * Either pass in a list of column widths or make separate calls to ->col()
     * to set each width as needed after calling ->grid().
     *
     * @param mixed $cols Pass in an array or a variable length argument list
     *                    to immediately set grid column widths.
     */
    public function grid($cols = null);

    /**
     * Define a grid column width.
     *
     * Should only be called after ->grid()
     *
     * @param mixed $width Column width
     */
    public function col($width);

    /**
     * Add properties to the table or current row or cell.
     *
     * The properties are added based on the last method called. If a row was
     * just added before this method is called then the properties are applied
     * to the row. If a cell was just added then the properties are applied to
     * the cell. If no rows or cells have been added then the properties are
     * applied to the table.
     * 
     * @param mixed $key Property name, array or Properties instance
     * @param mixed $val Property value (only if $key is a name)
     * @return Table Always returns $this
     */
    public function prop($key, $val = null);
    
    /**
     * Return all rows in the table.
     *
     * Tables are special elements that have many children but due to how those
     * children relate the rows must be processed differently than other
     * elements that use getElements().
     */
    public function getRows();
    
    /**
     * Return the table grid
     */
    public function getGrid();
}
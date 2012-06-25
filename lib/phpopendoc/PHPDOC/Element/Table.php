<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 *
 */
namespace PHPDOC\Element;

use PHPDOC\Property\Properties,
    PHPDOC\Property\PropertiesInterface
    ;

/**
 * The Table element class represents a table and implements a "Chainable"
 * coding interface to make it easy to build complex tables without breaking
 * up your code.
 *
 * @example
 * <code>
    $tbl = Table::create()
        ->row()
            ->cell('one')
        ->row()
            ->cell('two');
 * </code>
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
class Table extends Element implements TableInterface, BlockInterface
{

    const CONTEXT_TABLE = 0;
    const CONTEXT_GRID  = 1;
    const CONTEXT_ROW   = 2;
    const CONTEXT_CELL  = 3;

    /**
     * @var array Table grid
     */
    protected $grid;

    /**
     * @var array Table rows
     */
    protected $rows;

    /**
     * @var integer Index to current row
     */
    protected $rowIdx;

    /**
     * @var integer Index to current cell
     */
    protected $cellIdx;

    /**
     * @var array Reference to current row
     */
    protected $rowRef;

    /**
     * @var array Reference to current cell
     */
    protected $cellRef;

    /**
     * @var string Track last context used
     */
    private $context;

    /**
     * @var array Parent table for nested tables.
     */
    private $parentTbl;

    /**
     * @var array A list of default properties for rows and cells
     */
    private $defaults;

    /**
     * @var array Cell rowspan information
     */
    private $rowspan;

    public function __construct($properties = null)
    {
        parent::__construct($properties);
        $this->grid = array();
        $this->rows = array();
        $this->rowspan = array();
        $this->defaults = array();
        $this->rowIdx = -1;
        $this->cellIdx = -1;
        $this->context = self::CONTEXT_TABLE;
    }

    /**
     * Create a new Table instance.
     *
     * This is a shortcut so the caller doesn't have to assign "new" to a
     * variable before they can build out the table.
     *
     * @param mixed $properties Table level properties
     * @return Table Returns a new Table instance.
     */
    public static function create($properties = null)
    {
        $tbl = new Table($properties);
        return $tbl;
    }

    public function setParent(TableInterface $parent = null)
    {
        $this->parentTbl = $parent;
    }

    public function getParent()
    {
        return $this->parentTbl;
    }

    /**
     * Created a new table at the current cell context.
     *
     * This is a shortcut that makes it easier to nest tables. In order to jump
     * back to the previous table in the current chain you must call ->end() to
     * end the nested table. When using Table::create() and you have nested
     * tables always be sure to call ::end() otherwise the last nested table
     * will be the one assigned to your variable and not the root table.
     *
     * @param mixed $properties Table level properties
     * @return Table Returns a new Table instance.
     */
    public function table($properties = null)
    {
        $tbl = new Table($properties);
        $tbl->setParent($this);
        $this->cell(array($tbl, ''));
        return $tbl;
    }

    /**
     * End the current nested table level.
     *
     * This allows the caller to build a nested table structure using one long
     * chain w/o having to break up their code. If no nested table exists then
     * this returns the current table instance.
     *
     * @param boolean $all If true all nested tables are ended immediately.
     */
    public function end($all = false)
    {
        // complete any remaining rowspan's otherwise they won't get rendered
        if ($this->rowspan) {
            // determine max columns defined
            $max = 0;
            foreach ($this->rows as $r) {
                $cols = 0;
                foreach ($r->getElements() as $c) {
                    $p = $c->getProperties();
                    if ($p->has('colspan')) {
                        $cols += $p->get('colspan');
                    } else {
                        $cols += 1;
                    }
                }
                $max = max($cols, $max);
            }
            // repeat untilwe have no more rowspans ...
            while ($this->rowspan) {
                $this->cellIdx += 1;

                // start a new row if we've reached the max
                if ($this->cellIdx > $max) {
                    $this->row();
                }

                if (isset($this->rowspan[ $this->cellIdx ])) {
                    $this->updateRowspan();
                } else {
                    $this->cell('');
                }

            }
        }

        if (!$all) {
            return $this->parentTbl ? $this->parentTbl->end() : $this;
        }

        if ($this->parentTbl) {
            $parent = $this->parentTbl;
            while ($parent) {
                $parent->end();
                if ($parent->parentTbl) {
                    $parent = $parent->parentTbl;
                } else {
                    return $parent;
                }
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function grid($cols = null)
    {
        $this->context = self::CONTEXT_GRID;
        if (func_num_args()) {
            foreach (func_get_args() as $width) {
                if (is_array($width)) {
                    foreach ($width as $w) {
                        $this->col($w);
                    }
                } else {
                    $this->col($width);
                }
            }
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function col($width)
    {
        // @todo Is this really necessary? Technically speaking a gridCol can
        //       can be defined anywhere w/o affecting the current context.
        //       But for now lets keep the interface the same as other contexts.
        if ($this->context != self::CONTEXT_GRID) {
            $trace = debug_backtrace();
            throw new ElementException("Not in grid context. Call grid() first at {$trace[0]['file']}:{$trace[0]['line']}");
        }

        $this->grid[] = $width;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function row($properties = null)
    {
        $this->context = self::CONTEXT_ROW;
        $this->rowIdx += 1;
        $this->rows[ $this->rowIdx ] = new TableRow($this->_createProperties($properties, 'row'));
        $this->rowRef =& $this->rows[ $this->rowIdx ];

        // restart cell index
        $this->cellIdx = -1;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function cell($elements = null, $properties = null)
    {
        // start a new row if it hasn't been started already
        if (!$this->rowRef) {
            // @todo Maybe an exception should be thrown instead so the caller
            //       knows they've done something potentially stupid.
            $this->row();
        }
        $this->context = self::CONTEXT_CELL;

        $this->cellIdx += 1;

        // if there is a rowspan in our column in a row above us we need to
        // continue it before adding the new cell from the caller.
        $this->updateRowspan();

        $this->cellRef = new TableCell();
        $this->rowRef->addElement($this->cellRef);

        $this->add($elements);
        $this->prop($this->_createProperties($properties, 'cell'));

        return $this;
    }

    /**
     * Helper function that updated the current rowspan (only if needed).
     *
     * This method does not have to be called when merging rows via rowspan's.
     * this is merely a helper method to allow the user to keep their cell
     * structure neat. However, if you do use this where merged cells would
     * normally be located in your table there will be no need to call ->end()
     * at the end of the table chain.
     */
    public function merged()
    {
        return $this->updateRowspan();
    }

    private function updateRowspan()
    {
        if (isset($this->rowspan[ $this->cellIdx ])) {
            if ($this->rowspan[ $this->cellIdx ] > 1) {
                $this->rowspan[ $this->cellIdx ] --;
                if ($this->rowspan[ $this->cellIdx ] <= 1) {
                    unset($this->rowspan[ $this->cellIdx ]);
                }
                // merged cells must have an empty block element, eg: <w:p/>
                $this->cell('', array('rowspan' => 'continue'));
            } else {
                unset($this->rowspan[ $this->cellIdx ]);
            }
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function add($elements)
    {
        if ($elements === null) {
            return $this;
        }
        if (!$this->cellRef) {
            throw new ElementException("No cells are defined. Call cell() first.");
        }

        if (!is_array($elements)) {
            $elements = array( $elements );
        }

        foreach ($elements as $e) {
            if (($e instanceof LinkInterface) or !($e instanceof BlockInterface)) {
                $e = new Paragraph($e, $e instanceof ElementInterface ? $e->getProperties() : null);
            }
            $this->cellRef->addElement($e);
        }

        return $this;
    }

    /**
     * Shortcut for enabling the "cantSplit" row property for all rows
     */
    public function cantSplit($val = true)
    {
        $this->rowDefaults(array('cantSplit' => (bool)$val));
        return $this;
    }

    public function skipBefore($count)
    {
        if ($count) {
            if (!$this->rowRef) {
                $trace = debug_backtrace();
                throw new ElementException("No rows are defined. Call row() first at {$trace[0]['file']}:{$trace[0]['line']}");
            }
            $this->rowRef->getProperties()->set('skipBefore', $count);
        }
        return $this;
    }

    public function skipAfter($count)
    {
        if ($count) {
            if (!$this->rowRef) {
                $trace = debug_backtrace();
                throw new ElementException("No rows are defined. Call row() first at {$trace[0]['file']}:{$trace[0]['line']}");
            }
            $this->rowRef->getProperties()->set('skipAfter', $count);
        }
        return $this;
    }

    /**
     * Shortcut for adding a "rowspan" to a cell
     *
     * @param integer $rows The number of rows to span the cell vertically
     */
    public function rowspan($rows)
    {
        if ($this->context != self::CONTEXT_CELL) {
            $trace = debug_backtrace();
            throw new ElementException("Not in cell context. Call cell() first at {$trace[0]['file']}:{$trace[0]['line']}");
        }
        if (!$this->cellRef) {
            $trace = debug_backtrace();
            throw new ElementException("No cells are defined. Call cell() first at {$trace[0]['file']}:{$trace[0]['line']}");
        }

        return $this->prop('rowspan', $rows);
    }


    /**
     * Shortcut for adding a "colspan" to a cell
     *
     * @param integer $cols The number of columns to span the cell horizontally
     */
    public function colspan($cols)
    {
        if ($this->context != self::CONTEXT_CELL) {
            $trace = debug_backtrace();
            throw new ElementException("Not in cell context. Call cell() first at {$trace[0]['file']}:{$trace[0]['line']}");
        }
        if (!$this->cellRef) {
            $trace = debug_backtrace();
            throw new ElementException("No cells are defined. Call cell() first at {$trace[0]['file']}:{$trace[0]['line']}");
        }

        return $this->prop('colspan', $cols);
    }

    /**
     * {@inheritdoc}
     */
    public function prop($key, $val = null)
    {
        switch ($this->context) {
            case self::CONTEXT_TABLE:
                $prop = $this->getProperties();
                break;
            case self::CONTEXT_ROW:
                $prop = $this->rowRef->getProperties();
                break;
            case self::CONTEXT_CELL:
                $prop = $this->cellRef->getProperties();
                break;
            case self::CONTEXT_GRID:
                throw new ElementException("Table grids do not have properties");
            default:
                throw new ElementException("Unknown table context ($this->context)");
        }

        if (($key instanceof PropertiesInterface)) {
            // overwrite current properties with new ones
            $prop->clear();
            $prop->set($key->all());
        } else {
            $prop->set($key, $val);
        }

        if ($this->context == self::CONTEXT_CELL) {
            // if a "rowspan" is specified we handle it carefully ...
            if ($prop->has('rowspan')
                and is_numeric($prop['rowspan'])
                and !isset($this->rowspan[ $this->cellIdx ])) {
                $this->rowspan[ $this->cellIdx ] = $prop['rowspan'];
            }

            if ($prop->has('colspan')) {
                $this->cellIdx += $prop['colspan'] - 1;
            }
        }

        return $this;
    }

    /**
     * Set row property defaults.
     *
     * Any new row will inherit the default properties defined.
     */
    public function rowDefaults($properties)
    {
        if (!($properties instanceof PropertiesInterface)) {
            $properties = $this->_createProperties($properties);
        }
        if (!isset($this->defaults['row'])) {
            $this->defaults['row'] = $properties;
        } else {
            // merge new defaults to existing
            foreach ($properties as $k => $v) {
                $this->defaults['row'][$k] = $v;
            }
        }
        return $this;
    }

    /**
     * Set cell property defaults.
     *
     * Any new cell will inherit the default properties defined.
     */
    public function cellDefaults($properties)
    {
        if (!($properties instanceof PropertiesInterface)) {
            $properties = $this->_createProperties($properties);
        }
        if (!isset($this->defaults['cell'])) {
            $this->defaults['cell'] = $properties;
        } else {
            // merge new defaults to existing
            foreach ($properties as $k => $v) {
                $this->defaults['cell'][$k] = $v;
            }
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * {@inheritdoc}
     */
    public function getGrid()
    {
        return $this->grid;
    }

    /**
     * {@inheritdoc}
     */
    public function getElements()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function hasElements()
    {
        return false;
    }

    /**
     * @internal Creates a new Properties object
     * @throws ElementException
     */
    private function _createProperties($properties = array(), $defaults = null)
    {
        if (is_array($properties) or $properties === null) {
            $properties = new Properties($properties);
        }
        if (!($properties instanceof PropertiesInterface)) {
            throw new ElementException("Invalid properties object. Must be a PropertiesInterface.");
        }

        // assign defaults; but do not overwrite existing keys
        if (isset($this->defaults[$defaults])) {
            foreach ($this->defaults[$defaults] as $k => $v) {
                if (!isset($properties[$k])) {
                    $properties[$k] = $v;
                }
            }
        }
        return $properties;
    }
}

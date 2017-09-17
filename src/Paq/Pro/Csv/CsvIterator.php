<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\Pro\Csv;


class CsvIterator implements \Iterator
{
    const ROW_SIZE = 0;

    /**
     * The pointer to the cvs file.
     * @var resource
     */
    private $filePointer = null;

    /**
     * The current element, which will
     * be returned on each iteration.
     * @var array
     */
    private $currentElement = null;

    /**
     * The row counter.
     * @var int
     */
    private $rowCounter = null;

    /**
     * The delimiter for the csv file.µ
     * @var string
     */
    private $delimiter = null;

    /**
     * @var callable
     */
    private $formatter = null;

    /**
     * This is the constructor.It try to open the csv file.The method throws an exception
     * on failure.
     *
     * @access public
     * @param str $file The csv file.
     * @param str $delimiter The delimiter.
     *
     * @throws Exception
     */
    public function __construct($file, $delimiter = ',')
    {
        try {
            if (! is_resource($file)) {
                $this->filePointer = fopen($file, 'r');
            } else {
                $this->filePointer = $file;
            }
            $this->delimiter = $delimiter;
        } catch (\Exception $e) {
            throw new \Exception('The file "' . $file . '" cannot be read.');
        }
    }

    public function __destruct()
    {
        try {
            fclose($this->filePointer);
        } catch (\Exception $ex) {
            // I tried ...
        }
    }

    public function setFormatter(callable $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * This method resets the file pointer.
     *
     * @access public
     */
    public function rewind()
    {
        $this->rowCounter = 0;
        rewind($this->filePointer);
    }

    /**
     * This method returns the current csv row as a 2 dimensional array
     *
     * @access public
     * @return array The current csv row as a 2 dimensional array
     */
    public function current()
    {
        if (null === $this->currentElement) {
            $this->next();
        }

        if (null === $this->formatter) {
            return $this->currentElement;
        } else {
            $callable = $this->formatter;
            return $callable($this->currentElement);
        }
    }

    /**
     * This method returns the current row number.
     *
     * @access public
     * @return int The current row number
     */
    public function key()
    {
        return $this->rowCounter;
    }

    /**
     * This method checks if the end of file is reached.
     *
     * @access public
     * @return boolean Returns true on EOF reached, false otherwise.
     */
    public function next()
    {
        $this->currentElement = fgetcsv($this->filePointer, self::ROW_SIZE, $this->delimiter);

        $this->rowCounter++;
    }

    /**
     * This method checks if the next row is a valid row.
     *
     * @access public
     * @return boolean If the next row is a valid row.
     */
    public function valid()
    {
        return !feof($this->filePointer);
    }
}
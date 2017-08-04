<?php
/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license Modified MIT License
 * @link https://blueimp.net/ajax/
 */

// Class to perform SQL (MySQL) queries:
class AJAXChatMySQLQuery
{
    protected $_connectionID;
    protected $_sql = '';
    protected $_result = 0;
    protected $_errno = 0;
    protected $_error = '';

    // Constructor:
    public function __construct($sql, $connectionID = null)
    {
        $this->_sql = trim($sql);
        $this->_connectionID = $connectionID;
        if ($this->_connectionID) {
            $this->_result = mysql_query($this->_sql, $this->_connectionID);
            if (!$this->_result) {
                $this->_errno = mysql_errno($this->_connectionID);
                $this->_error = mysql_error($this->_connectionID);
            }
        } else {
            $this->_result = mysql_query($this->_sql);
            if (!$this->_result) {
                $this->_errno = mysql_errno();
                $this->_error = mysql_error();
            }
        }
    }

    // Returns true if an error occured:

    public function getError()
    {
        if ($this->error()) {
            $str = 'Query: ' . $this->_sql . "\n";
            $str .= 'Error-Report: ' . $this->_error . "\n";
            $str .= 'Error-Code: ' . $this->_errno;
        } else {
            $str = 'No errors.';
        }

        return $str;
    }

    // Returns an Error-String:

    public function error()
    {
        // Returns true if the Result-ID is valid:
        return !(bool)($this->_result);
    }

    // Returns the content:

    public function fetch()
    {
        if ($this->error()) {
            return null;
        } else {
            return mysql_fetch_assoc($this->_result);
        }
    }

    // Returns the number of rows (SELECT or SHOW):
    public function numRows()
    {
        if ($this->error()) {
            return null;
        } else {
            return mysql_num_rows($this->_result);
        }
    }

    // Returns the number of affected rows (INSERT, UPDATE, REPLACE or DELETE):
    public function affectedRows()
    {
        if ($this->error()) {
            return null;
        } else {
            return mysql_affected_rows($this->_connectionID);
        }
    }

    // Frees the memory:
    public function free()
    {
        @mysql_free_result($this->_result);
    }
}
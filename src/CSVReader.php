<?php

namespace lantongxue;

use \Iterator;
use \Exception;

class CSVReader implements Iterator
{
    protected $csvFile;

    protected $offset = 0;

    protected $header = [];

    protected $headerOffset = 0;

    protected $firstRowIsHeader = true;

    protected $utf8WithBom = false;

    protected $utf8Bom = "\xEF\xBB\xBF";

    protected $csvHandle = null;

    protected $position = 0;

    protected $size = 0;

    protected $resetWindow = true;

    private $_row = [];

    private $_getcsvParams = [
        'separator' => ",",
        'enclosure' => "\"",
        'escape' => "\\",
    ];

    public function __construct(string $csvFile, bool $firstRowIsHeader = true, array $getcsvParams = [])
    {
        $this->csvFile = $csvFile;
        $this->firstRowIsHeader = $firstRowIsHeader;
        $this->_getcsvParams = array_merge($this->_getcsvParams, $getcsvParams);
        
        if(!file_exists($csvFile)) {
            throw new Exception("File not found: {$csvFile}");
        }

        if(PHP_OS == 'WINNT') {
            setlocale(LC_CTYPE, 'zh-CN.UTF-8');
        } else {
            setlocale(LC_CTYPE, 'zh_CN.UTF-8');
        }

        $this->csvHandle = fopen($this->csvFile, 'r');

        $this->size = filesize($this->csvFile);

        $this->utf8WithBom = fread($this->csvHandle, 3) == $this->utf8Bom;

        $this->readHeader();
    }

    protected function readHeader(): void
    {
        if($this->firstRowIsHeader) {
            if($this->utf8WithBom) {
                fseek($this->csvHandle, 3);
            } else {
                fseek($this->csvHandle, 0);
            }
            $this->header = fgetcsv($this->csvHandle, null, $this->_getcsvParams['separator'], $this->_getcsvParams['enclosure'], $this->_getcsvParams['escape']);
            $this->headerOffset = ftell($this->csvHandle);
        }
    }

    public function GetHeader(): array
    {
        return $this->header;
    }

    public function Chunk(callable $fn, int $limit = 1000): void
    {
        $this->resetWindow();
        do {
            $rows = [];
            for($i = 0; $i < $limit; ++$i) {
                if($this->read()) {
                    $rows[] = $this->_row;
                }
            }
            $ret = call_user_func($fn, $rows);
        } while($this->valid() && $ret);
    }

    protected function read(): bool
    {
        $row = fgetcsv($this->csvHandle, null, $this->_getcsvParams['separator'], $this->_getcsvParams['enclosure'], $this->_getcsvParams['escape']);
        $this->offset = ftell($this->csvHandle);
        if(feof($this->csvHandle)) {
            return false;
        }
        if($this->firstRowIsHeader) {
            $row = array_combine($this->header, $row);
        }
        $this->_row = $row;
        ++$this->position;
        return true;
    }

    public function current(): array
    {
        return $this->_row;
    }
    
    /**
     * @inheritDoc
     */
    public function key(): int
    {
        return $this->position;
    }
    
    /**
     * @inheritDoc
     */
    public function next(): void
    {
        $this->read();
    }
    
    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        if($this->resetWindow) {
            $this->resetWindow();
        }
        $this->read();
    }

    public function resetWindow(): void
    {
        fseek($this->csvHandle, $this->headerOffset);
        $this->offset = 0;
        $this->position = 0;
        $this->_row = null;
    }
    
    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return !feof($this->csvHandle);
    }

    public function __destruct()
    {
        fclose($this->csvHandle);
    }
}
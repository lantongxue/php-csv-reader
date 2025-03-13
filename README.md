# CSVReader
A high-performance and low-memory CSV reader

# Usage
CSVReader constructor parameters：

```php
CSVReader(string $csvFile, bool $firstRowIsHeader = true, array $getcsvParams = [])
```

`$csvFile` Specify CSV file path

`$firstRowIsHeader` Setting the first row as the header row, default `true`

`$getcsvParams` This parameter exists for compatibility with php8, but also improves the flexibility of csv reading, the specific parameter structure is as follows:

```php
[
    'separator' => ",",
    'enclosure' => "\"",
    'escape' => "\\",
];
```
Refer to the <a hre="https://www.php.net/manual/zh/function.fgetcsv.php" target="_blank">fgetcsv</a> function for the exact meaning.

## Reading through an iterator
```php
$csv = 'test.csv';
$reader = new lantongxue\CSVReader($csv);
foreach($reader as $row) {
    // todo
}
```

## Chunked read
```php
$csv = 'test.csv';
$reader = new lantongxue\CSVReader($csv);
$reader->Chunk(function($rows) {
    foreach($rows as $row) {
        // todo
    }
    return true;
}, 1000);
```

# Other Api
`GetHeader()` Return header row when the `$firstRowIsHeader` is `true`

# License
MIT
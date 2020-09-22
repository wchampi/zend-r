<?php

class ZendR_Data_Csv
{
    public static function loadFile($file, $separator)
    {
        $rows = array();
        if (($gestor = fopen($file, "r")) !== FALSE) {
            while (($columns = fgetcsv($gestor, 1000, trim($separator))) !== FALSE) {
                $rows[] = $columns;
            }
            fclose($gestor);
        }
        return $rows;
    }

    public static function importTable($table, $columns, $file, $separator, Doctrine_Connection $conn)
    {
        $query = '';
        $rowsDb = array();
        $rows = self::loadFile($file, $separator);
        foreach ($rows as $cont => $row) {
            $rowDb = array();
            foreach ($row as $valor) {
                $valor = ZendR_String::parseString(trim($valor))->forDB()->__toString();
                $rowDb[] = "'" . $valor . "'";
            }
            $line = '(' . implode(',', $rowDb) . ')';
            $rowsDb[] = $line;

            if ($cont % 10000 == 0) {
                $query = "INSERT INTO " . $table . ' (' . $columns . ') VALUES ' . implode(',', $rowsDb) . ';';
                $conn->exec($query);
                $rowsDb = array();
            }
        }

        if (count($rowsDb) > 0) {
            $query = "INSERT INTO " . $table . ' (' . $columns . ') VALUES ' . implode(',', $rowsDb) . ';';
            $conn->exec($query);
        }
    }

    public static function export($query, $file, $separator, $conn, $titulos = '')
    {
        file_put_contents($file, @iconv('UTF-8', 'Windows-1252//TRANSLIT', ZendR_String::parseString($titulos)->toUTF8()->__toString()) . "\n");

        $stm = $conn->prepare($query);
        $stm->execute();

        $rows = $stm->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            self::filePutCsv($file, $row);
        }
    }

    public static function filePutCsv($file, array $row, $codification = null)
    {
        $columns = array();
        foreach ($row as $val) {
            $columns[] = self::prepareString($val, $codification);
        }
        $rowString = '"' . implode('","', $columns) . '"' . "\n";
        file_put_contents($file, $rowString, FILE_APPEND);
    }
    
    public static function prepareString($string, $codification = null, $toCodification = 'Windows-1252')
    {
            $string = str_replace(array(chr(13), chr(10), '"'), array(' ', ' ', '""'), $string);
        if ($codification == null) {
            $encode = ZendR_String::parseString($string)->encode();
            if ($encode == ISO_8859_1) {
                return @iconv('ISO-8859-1', $toCodification . '//TRANSLIT//IGNORE', $string);
            } elseif ($encode == UTF_8) {
                return @iconv('UTF-8', $toCodification . '//TRANSLIT//IGNORE', $string);
            } elseif ($encode == ASCII) {
                return @iconv('ISO-8859-1', $toCodification . '//TRANSLIT//IGNORE', $string);
            } else {
                return $string;
            }
        } else {
            return @iconv($codification, $toCodification . '//TRANSLIT//IGNORE', $string);
        }
    }

    public static function prepareFieldVal($val)
    {
        return '"' . str_replace('"', '""', $val) . '"';
    }
}

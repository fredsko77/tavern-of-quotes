<?php

namespace Import\Service;

use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait ImportTrait
{

    /**
     * @param UploadedFile|null $file
     * 
     * @return array|null
     */
    public function parseCsv(?UploadedFile $file = null): ?array
    {
        $data = [];
        try {
            if (($handle = fopen($file->getRealPath(), "r")) !== FALSE) {
                $i = 0;
                while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) {
                    $data[$i] = $row;
                    $i++;
                }
                fclose($handle);
            }
        } catch (Exception $e) {
            throw new Exception("An error occured when parsing the data from csv ! {$e->getMessage()}");
        }

        return count($data) > 1 ? $data : null;
    }

    /**
     * [getDataFromCsv get data from parsed csv file] 
     *
     * @param UploadedFile|null $file
     * 
     * @return array|null
     */
    public function getDataFromCsv(?UploadedFile $file = null): ?array
    {
        $rawData = $this->parseCsv($file);

        if (is_null($rawData)) {
            return $rawData;
        }

        $columns = $rawData[0];
        unset($rawData[0]);
        $rows = $rawData;

        $data = [];

        foreach ($rows as $i => $row) {
            $object = [];
            foreach ($row as $k => $v) {
                $object[$columns[$k]] = $v;
            }
            $data[$i] = (object) $object;
        }

        return $data;
    }
}

<?php

namespace Sentinnel\CoreBundle\Manager;


class LogsManager
{
    public function findAll($file, $start = null, $end = null)
    {
        if (!file_exists($file)) {
            return array();
        }

        $file = fopen($file, 'r');
        fseek($file, 0, SEEK_END);

        $result = array();
        while ($line = $this->readLineFromFile($file)) {
//            $values = str_getcsv($line);
//            list($csvToken, $csvIp, $csvMethod, $csvUrl, $csvTime, $csvParent) = $values;
//            $csvStatusCode = isset($values[6]) ? $values[6] : null;
//
//            $csvTime = (int) $csvTime;
//
//            if (!empty($start) && $csvTime < $start) {
//                continue;
//            }
//
//            if (!empty($end) && $csvTime > $end) {
//                continue;
//            }

            $result[] = $line;
        }

        fclose($file);

        return array_values($result);
    }

    /**
     * Reads a line in the file, backward.
     *
     * This function automatically skips the empty lines and do not include the line return in result value.
     *
     * @param resource $file The file resource, with the pointer placed at the end of the line to read
     *
     * @return mixed A string representing the line or null if beginning of file is reached
     */
    protected function readLineFromFile($file)
    {
        $line = '';
        $position = ftell($file);

        if (0 === $position) {
            return;
        }

        while (true) {
            $chunkSize = min($position, 1024);
            $position -= $chunkSize;
            fseek($file, $position);

            if (0 === $chunkSize) {
                // bof reached
                break;
            }

            $buffer = fread($file, $chunkSize);

            if (false === ($upTo = strrpos($buffer, "\n"))) {
                $line = $buffer.$line;
                continue;
            }

            $position += $upTo;
            $line = substr($buffer, $upTo + 1).$line;
            fseek($file, max(0, $position), SEEK_SET);

            if ('' !== $line) {
                break;
            }
        }

        return '' === $line ? null : $line;
    }
}

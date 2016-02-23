<?php

namespace Sentinnel\CoreBundle\Manager;


use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\Yaml\Yaml;

class ProfilerManager
{
    /**
     * @var string
     */
    private $folder;

    /**
     * @param $folder
     */
    function __construct($folder)
    {
        $this->folder = $folder;
    }

    public function find($ip, $url, $limit, $method, $start = null, $end = null)
    {
        $file = $this->getIndexFilename();

        if (!file_exists($file)) {
            return array();
        }

        $file = fopen($file, 'r');
        fseek($file, 0, SEEK_END);

        $result = array();
        while (count($result) < $limit && $line = $this->readLineFromFile($file)) {
            $values = str_getcsv($line);
            list($csvToken, $csvIp, $csvMethod, $csvUrl, $csvTime, $csvParent) = $values;
            $csvStatusCode = isset($values[6]) ? $values[6] : null;

            $csvTime = (int) $csvTime;

            if ($ip && false === strpos($csvIp, $ip) || $url && false === strpos($csvUrl, $url) || $method && false === strpos($csvMethod, $method)) {
                continue;
            }

            if (!empty($start) && $csvTime < $start) {
                continue;
            }

            if (!empty($end) && $csvTime > $end) {
                continue;
            }

            $result[$csvToken] = array(
                'token' => $csvToken,
                'ip' => $csvIp,
                'method' => $csvMethod,
                'url' => $csvUrl,
                'time' => $csvTime,
                'parent' => $csvParent,
                'status_code' => $csvStatusCode,
            );
        }

        fclose($file);

        return array_values($result);
    }

    public function findAll($start = null, $end = null)
    {
        $file = $this->getIndexFilename();

        if (!file_exists($file)) {
            return array();
        }

        $file = fopen($file, 'r');
        fseek($file, 0, SEEK_END);

        $result = array();
        while ($line = $this->readLineFromFile($file)) {
            $values = str_getcsv($line);
            list($csvToken, $csvIp, $csvMethod, $csvUrl, $csvTime, $csvParent) = $values;
            $csvStatusCode = isset($values[6]) ? $values[6] : null;

            $csvTime = (int) $csvTime;

            if (!empty($start) && $csvTime < $start) {
                continue;
            }

            if (!empty($end) && $csvTime > $end) {
                continue;
            }

            $result[$csvToken] = array(
                'token' => $csvToken,
                'ip' => $csvIp,
                'method' => $csvMethod,
                'url' => $csvUrl,
                'time' => $csvTime,
                'parent' => $csvParent,
                'status_code' => $csvStatusCode,
            );
        }

        fclose($file);

        return array_values($result);
    }

    /**
     * Gets filename to store data, associated to the token.
     *
     * @param string $token
     *
     * @return string The profile filename
     */
    protected function getFilename($token)
    {
        // Uses 4 last characters, because first are mostly the same.
        $folderA = substr($token, -2, 2);
        $folderB = substr($token, -4, 2);

        return $this->folder.'/'.$folderA.'/'.$folderB.'/'.$token;
    }

    /**
     * Gets the index filename.
     *
     * @return string The index filename
     */
    protected function getIndexFilename()
    {
        return $this->folder.'/index.csv';
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

    public function read($token)
    {
        if (!$token || !file_exists($file = $this->getFilename($token))) {
            return;
        }

        return $this->createProfileFromData($token, unserialize(file_get_contents($file)));
    }

    protected function createProfileFromData($token, $data, $parent = null)
    {
        $profile = new Profile($token);
        $profile->setIp($data['ip']);
        $profile->setMethod($data['method']);
        $profile->setUrl($data['url']);
        $profile->setTime($data['time']);
        $profile->setCollectors($data['data']);

        if (!$parent && $data['parent']) {
            $parent = $this->read($data['parent']);
        }

        if ($parent) {
            $profile->setParent($parent);
        }

        foreach ($data['children'] as $token) {
            if (!$token || !file_exists($file = $this->getFilename($token))) {
                continue;
            }

            $profile->addChild($this->createProfileFromData($token, unserialize(file_get_contents($file)), $profile));
        }

        return $profile;
    }
}

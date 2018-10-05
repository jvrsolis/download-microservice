<?php

namespace App\Services;

use ByteUnits\Metric as ByteUnit;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as LocalStorage;

use function ByteUnits\parse as parseUnits;

/**
 * Class DownloadService
 *
 * A reusable service class utilized as a
 * singleton in the console application.
 * Its purpose is to contain any logic
 * associated with downloading a file.
 * Currently only supports downloading partial/whole
 * files using a range request. However, this class
 * has been built to accommodate other methods related
 * to the downloading files. No implementation is done
 * here. Any implementation is extracted to points of use
 * classes.
 *
 * @package App\Services
 * @author  Javier Solis <jvrsolis@outlook.com>
 */
class DownloadService
{
    /**
     * @var string
     */
    const RANGE = 'Range';

    /**
     * @var string
     */
    const ACCEPTSRANGES = 'Accept-Ranges';

    /**
     * @var string
     */
    const CONTENTLENGTH = 'Content-Length';

    /**
     * @var string
     */
    const METHOD = 'GET';

    /**
     * @var \GuzzleHttp\Psr7\Uri
     */
    protected $sourceUrl;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var  \ByteUnits\System
     */
    protected $downloadSize;

    /**
     * @var  \ByteUnits\System
     */
    protected $chunkSize;

    /**
     * @var string
     */
    protected $fullpath;

    /**
     * @var \League\Flysystem\Filesystem
     */
    protected $filesystem;

    /**
     * DownloadService constructor.
     */
    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Return the Uri instance.
     * 
     * @return \GuzzleHttp\Psr7\Uri
     */
    public function url()
    {
        return $this->sourceUrl;
    }

    /**
     * Return the download size instance.
     *
     * @return \ByteUnits\System
     */
    public function downloadSize()
    {
        return $this->downloadSize;
    }

    /**
     * Return the chunk size instance.
     *
     * @return \ByteUnits\System
     */
    public function chunkSize()
    {
        return $this->chunkSize;
    }

    /**
     * Return the full output path
     * of the file.
     *
     * @return string
     */
    public function fullpath()
    {
        return $this->fullpath;
    }

    /**
     * Return only the file path
     * excluding the file name.
     *
     * @return string
     */
    public function filepath()
    {
        return dirname($this->fullpath);
    }

    /**
     * Return only the filename
     * excluding the file path.
     *
     * @return string
     */
    public function filename()
    {
        return basename($this->fullpath);
    }

    /**
     * Return the current size of
     * the downloaded file.
     *
     * @return int
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function currentSize()
    {
        if ($this->fileExists($this->filename())) {
            return (int)$this->filesystem->getSize($this->filename());
        }

        return 0;
    }

    /**
     * Return the percent downloaded as an integer or
     * float, return false if source url cannot provide
     * the content-length.
     *
     * @return bool|float|int
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function percentDownloaded()
    {
        if (!$this->contentLength()) {
            return false;
        }

        return ($this->currentSize() / $this->contentLength()) * 100;
    }

    /**
     * Return the remaining bytes to download as an
     * integer, return false if source url
     * cannot provide the content-length.
     *
     * @return bool|int
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function remainingSize()
    {
        if (!$this->contentLength()) {
            return false;
        }

        $remainingSize = $this->contentLength() - $this->currentSize();

        if ($remainingSize < 0) {
            $remainingSize = 0;
        }

        return $remainingSize;
    }

    /**
     * Return the percent remaining to download as an
     * integer or float, return false if source url
     * cannot provide the content-length.
     *
     * @return bool|float|int
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function percentRemaining()
    {
        if (!$this->contentLength()) {
            return false;
        }

        return (($this->contentLength() - $this->currentSize()) / $this->contentLength()) * 100;
    }

    /**
     * Return the content length integer
     * as a string, return false if source url cannot
     * provide the content length.
     *
     * @return bool|string
     */
    public function contentLength()
    {
        $length = (int)static::parseHeaders(self::CONTENTLENGTH, get_headers($this->sourceUrl)) ? : false;

        return $length;
    }

    /**
     * Determine if the source url request
     * can accept the range header.
     *
     * @return bool
     */
    public function acceptsRanges()
    {
        return (bool)static::parseHeaders(self::ACCEPTSRANGES, get_headers($this->sourceUrl));
    }

    /**
     * Return the expected number of requests.
     *
     * @return int
     */
    public function numberOfRequests()
    {
        return (int)ceil($this->downloadSize->numberOfBytes() / $this->chunkSize->numberOfBytes());
    }

    /**
     * Build the range request by preparing
     * basic request parameters.
     *
     * @param $sourceUrl
     * @param $downloadSize
     * @param $chunkSize
     * @param $destinationPath
     */
    public function buildRangeRequest($sourceUrl, $downloadSize, $chunkSize, $destinationPath = null)
    {
        $this->sourceUrl = static::toUri($sourceUrl);

        $this->downloadSize = static::toByteUnits($downloadSize); // ASSUMPTION: Assumes bytes are strings containing the integer value and units size.

        $this->chunkSize = static::toByteUnits($chunkSize); // ASSUMPTION: Assumes bytes are strings containing the integer value and units size.

        $this->fullpath = $destinationPath ? : storage_path() . "/" . basename($sourceUrl);

        static::validateSizing($downloadSize, $chunkSize);

        $this->localScope($this->filepath()); // ASSUMPTION: Assumes local scope is output file destination  (can be made dynamic however).
    }

    /**
     * Request a range of bytes from the source url,
     * return the response.
     *
     * @param $from
     * @param $to
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function requestRange($from, $to)
    {
        $requestRange = new Request(self::METHOD, $this->sourceUrl, [self::RANGE => sprintf("bytes=%d-%d", $from, $to)]);

        return $this->client->send($requestRange);
    }

    /**
     * Write the file to a disk.
     *
     * @param string $contentToBeAdded
     * @throws \League\Flysystem\FileExistsException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function writeToFile($contentToBeAdded = '')
    {
        if (!$this->fileExists($this->filename())) {
            $this->filesystem->write($this->filename(), $contentToBeAdded, [
                'visibility' => 'public'
            ]);
        } else {
            $content = $this->filesystem->read($this->filename());

            $content .= $contentToBeAdded;

            $this->filesystem->update($this->filename(), $content, [
                'visibility' => 'public'
            ]);
        }
    }

    /**
     * Convert a string url into an
     * validated object.
     *
     * @param string $url
     * @return \GuzzleHttp\Psr7\Uri
     */
    protected static function toUri($url)
    {
        return new Uri($url);
    }

    /**
     * Convert a string or integer into a
     * ByteUnit object.
     *
     * @param $bytes
     * @return \ByteUnits\System
     */
    protected static function toByteUnits($bytes)
    {
        return parseUnits($bytes);
    }

    /**
     * Obtain a real path if relative paths
     * are provided.
     *
     * @param $path
     * @return bool|string
     */
    protected static function toRealPath($path)
    {
        return realpath($path);
    }

    /**
     * Scope the filesystem object to only write and
     * read from the specified local filepath.
     *
     * @param string $filepath
     */
    protected function localScope($filepath)
    {
        $localStorage = new LocalStorage($filepath);
        $this->filesystem = new Filesystem($localStorage);
    }

    /**
     * Check if the specified file exists in the filesystem
     * scope.
     *
     * @param string $filename
     * @return bool
     */
    protected function fileExists($filename)
    {
        return $this->filesystem->has($filename);
    }

    /**
     * Return the header value of a response.
     *
     * @param string $header
     * @param array $response
     *
     * @return string
     */
    protected static function parseHeaders($header, $response)
    {
        foreach ($response as $key => $r) {
            if (stripos($r, $header . ':') === 0) {
                list($headername, $headervalue) = explode(":", $r, 2);
                return trim($headervalue);
            }
        }

        return null;
    }

    /**
     * Validate the the chunk size is not greater
     * than the download size.
     *
     * @param $downloadSize
     * @param $chunkSize
     * @return bool
     */
    protected static function validateSizing($downloadSize, $chunkSize)
    {
        return $chunkSize > $downloadSize;
    }
}
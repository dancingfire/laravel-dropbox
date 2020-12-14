<?php

namespace Dcblogdev\Dropbox\Resources;

use Dcblogdev\Dropbox\Dropbox;
use GuzzleHttp\Client;
use Exception;

use function PHPUnit\Framework\throwException;
use function trigger_error;

class Files extends Dropbox
{
    public function __construct()
    {
        parent::__construct();
    }

    public function listContents($path = '')
    {
        $pathRequest = $this->forceStartingSlash($path);

        return $this->post('files/list_folder', [
            'path' => $path == '' ? '' : $pathRequest
        ]);
    }

    public function listContentsContinue($cursor = '')
    {
        return $this->post('files/list_folder/continue', [
            'cursor' => $cursor
        ]);
    }

    public function delete($path)
    {
        $path = $this->forceStartingSlash($path);

        return $this->post('files/delete_v2', [
            'path' => $path
        ]);
    }

    public function createFolder($path)
    {
        $path = $this->forceStartingSlash($path);

        return $this->post('files/create_folder', [
            'path' => $path
        ]);
    }

    public function search($query)
    {
        return $this->post('files/search', [
            'path' => '',
            'query' => $query,
            'start' => 0,
            'max_results' => 1000,
            'mode' => 'filename'
        ]);
    }
    /**
     * Allows the setting of a namespace variable
     */
    public function uploadWH($path, $uploadPath, $mode = 'add', $namespaceId)
    {
        if ($uploadPath == '') {
            throw new Exception('File is required');
        }

	    $path     = ($path !== '') ? $this->forceStartingSlash($path) : '';
	    $contents = $this->getContents($uploadPath);
        $filename = $this->getFilenameFromPath($uploadPath);
        $path     = $path.$filename;

        try {

            $ch = curl_init('https://content.dropboxapi.com/2/files/upload');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->getAccessToken(),
                'Content-Type: application/octet-stream',
                'Dropbox-API-Arg: ' .
                    json_encode([
                        "path" => $path,
                        "mode" => $mode,
                        "autorename" => true,
                        "mute" => false
                    ]),
                'Dropbox-API-Path-Root: ' . json_encode([
                    ".tag"=> "namespace_id",
                    "namespace_id"=>$namespaceId
                    ])
                ]
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $contents);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            return $response;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function upload($path, $uploadPath, $mode = 'add')
    {
        if ($uploadPath == '') {
            throw new Exception('File is required');
        }

	    $path     = ($path !== '') ? $this->forceStartingSlash($path) : '';
	    $contents = $this->getContents($uploadPath);
        $filename = $this->getFilenameFromPath($uploadPath);
        $path     = $path.$filename;

        try {

            $ch = curl_init('https://content.dropboxapi.com/2/files/upload');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->getAccessToken(),
                'Content-Type: application/octet-stream',
                'Dropbox-API-Arg: ' .
                    json_encode([
                        "path" => $path,
                        "mode" => $mode,
                        "autorename" => true,
                        "mute" => false
                    ])
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $contents);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            return $response;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function download($path)
    {
        $path = $this->forceStartingSlash($path);

        try {
            $client = new Client;

            $response = $client->post("https://content.dropboxapi.com/2/files/download", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                    'Dropbox-API-Arg' => json_encode([
                        'path' => $path
                    ])
                ]
            ]);

            return $response->getBody()->getContents();
        } catch (Exception $e) {
            throw new Exception($e->getResponse()->getBody()->getContents());
        }
    }

    public function download_zip($path, $namespaceId)
    {
        $path = $this->forceStartingSlash($path);

        try {
            $client = new Client;

            $response = $client->post("https://content.dropboxapi.com/2/files/download", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                    'Dropbox-API-Arg' => json_encode([
                        'path' => $path
                    ]),
                    'Dropbox-API-Path-Root: ' . json_encode([
                        ".tag"=> "namespace_id",
                        "namespace_id"=>$namespaceId
                        ])
                ]
            ]);

            return $response->getBody()->getContents();
        } catch (Exception $e) {
            throw new Exception($e->getResponse()->getBody()->getContents());
        }
    }

    protected function getFilenameFromPath($filePath)
    {
        $parts = explode('/', $filePath);
        $filename = end($parts);
        return $this->forceStartingSlash($filename);
    }

    protected function getContents($filePath)
    {
        return file_get_contents($filePath);
    }
}

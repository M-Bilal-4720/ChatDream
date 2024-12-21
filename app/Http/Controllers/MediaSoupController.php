<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class MediaSoupController extends Controller
{
    private $nodeServerUrl;

    public function __construct()
    {
        $this->nodeServerUrl = 'http://localhost:3000'; // Update with your Node.js server URL
    }

    public function createTransport(Request $request)
    {
        try {
            // Initialize Guzzle HTTP client
            $client = new Client();

            // Send the POST request to the external service (replace URL with the correct one)
            $response = $client->post('http://localhost:3000/create-transport', [
                'json' => $request->all(),  // Send the incoming request data as JSON
            ]);

            // Return the response from the external service
            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()->getContents())
            ], 200);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // Handle error in case the external service is not reachable or returns an error
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function connectTransport(Request $request)
    {
        $client = new Client();
        $response = $client->post("{$this->nodeServerUrl}/connect-transport", [
            'json' => [
                'transportId' => $request->input('transportId'),
                'dtlsParameters' => $request->input('dtlsParameters'),
            ]
        ]);

        return response()->json(json_decode($response->getBody()->getContents(), true));
    }

    public function produce(Request $request)
    {
        $client = new Client();
        $response = $client->post("{$this->nodeServerUrl}/produce", [
            'json' => [
                'transportId' => $request->input('transportId'),
                'kind' => $request->input('kind'),
                'rtpParameters' => $request->input('rtpParameters'),
            ]
        ]);

        return response()->json(json_decode($response->getBody()->getContents(), true));
    }

    public function consume(Request $request)
    {
        $client = new Client();
        $response = $client->post("{$this->nodeServerUrl}/consume", [
            'json' => [
                'transportId' => $request->input('transportId'),
                'rtpCapabilities' => $request->input('rtpCapabilities'),
            ]
        ]);

        return response()->json(json_decode($response->getBody()->getContents(), true));
    }

    public function getRouterRtpCapabilities()
    {
        try {
            // Initialize the Guzzle client
            $client = new Client();

            // Make a GET request to the Mediasoup Node.js server for router RTP capabilities
            $response = $client->get("{$this->nodeServerUrl}/router-rtp-capabilities");

            // Return the response data as JSON
            return response()->json(json_decode($response->getBody()->getContents(), true));
        } catch (\Exception $e) {
            // Handle errors gracefully
            return response()->json([
                'error' => 'Could not fetch RTP capabilities from Node Mediasoup server',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

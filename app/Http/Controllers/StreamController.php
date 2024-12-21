<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class StreamController extends Controller
{
protected $mediasoupUrl;

public function __construct()
{
$this->mediasoupUrl = 'http://localhost:3001'; // URL of the Mediasoup server
$this->client = new Client();
}

/**
* Create a WebRTC Transport.
*/
public function createTransport(Request $request)
{
try {
$response = $this->client->post("{$this->mediasoupUrl}/createTransport", [
'json' => [
'userId' => $request->user()->id,
]
]);

return response()->json(json_decode($response->getBody(), true));
} catch (\Exception $e) {
return response()->json(['error' => $e->getMessage()], 500);
}
}

/**
* Connect a WebRTC Transport.
*/
public function connectTransport(Request $request)
{
$transportId = $request->input('transportId');
$dtlsParameters = $request->input('dtlsParameters');

try {
$response = $this->client->post("{$this->mediasoupUrl}/connectTransport", [
'json' => [
'transportId' => $transportId,
'dtlsParameters' => $dtlsParameters,
]
]);

return response()->json(json_decode($response->getBody(), true));
} catch (\Exception $e) {
return response()->json(['error' => $e->getMessage()], 500);
}
}

/**
* Produce a media stream.
*/
public function produce(Request $request)
{
$transportId = $request->input('transportId');
$kind = $request->input('kind');
$rtpParameters = $request->input('rtpParameters');

try {
$response = $this->client->post("{$this->mediasoupUrl}/produce", [
'json' => [
'transportId' => $transportId,
'kind' => $kind,
'rtpParameters' => $rtpParameters,
]
]);

return response()->json(json_decode($response->getBody(), true));
} catch (\Exception $e) {
return response()->json(['error' => $e->getMessage()], 500);
}
}

/**
* Consume a media stream.
*/
public function consume(Request $request)
{
$transportId = $request->input('transportId');
$producerId = $request->input('producerId');
$rtpCapabilities = $request->input('rtpCapabilities');

try {
$response = $this->client->post("{$this->mediasoupUrl}/consume", [
'json' => [
'transportId' => $transportId,
'producerId' => $producerId,
'rtpCapabilities' => $rtpCapabilities,
]
]);

return response()->json(json_decode($response->getBody(), true));
} catch (\Exception $e) {
return response()->json(['error' => $e->getMessage()], 500);
}
}

/**
* Pause a consumer.
*/
public function pauseConsumer(Request $request)
{
$consumerId = $request->input('consumerId');

try {
$response = $this->client->post("{$this->mediasoupUrl}/pauseConsumer", [
'json' => [
'consumerId' => $consumerId,
]
]);

return response()->json(json_decode($response->getBody(), true));
} catch (\Exception $e) {
return response()->json(['error' => $e->getMessage()], 500);
}
}

/**
* Resume a consumer.
*/
public function resumeConsumer(Request $request)
{
$consumerId = $request->input('consumerId');

try {
$response = $this->client->post("{$this->mediasoupUrl}/resumeConsumer", [
'json' => [
'consumerId' => $consumerId,
]
]);

return response()->json(json_decode($response->getBody(), true));
} catch (\Exception $e) {
return response()->json(['error' => $e->getMessage()], 500);
}
}
}

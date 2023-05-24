<?php

declare(strict_types=1);

namespace Docker\Endpoint;

use Docker\API\Endpoint\ContainerAttachWebsocket as BaseEndpoint;
use Docker\Stream\AttachWebsocketStream;
use Docker\Stream\DockerRawStream;
use Nyholm\Psr7\Stream;
use Symfony\Component\Serializer\SerializerInterface;

class ContainerAttachWebsocket extends BaseEndpoint
{
    public function getExtraHeaders(): array
    {
        return \array_merge(
            parent::getExtraHeaders(),
            [
                'Host' => 'localhost',
                'Origin' => 'php://docker-php',
                'Upgrade' => 'websocket',
                'Connection' => 'Upgrade',
                'Sec-WebSocket-Version' => '13',
                'Sec-WebSocket-Key' => \base64_encode(\uniqid()),
            ]
        );
    }

    protected function transformResponseBody(\Psr\Http\Message\ResponseInterface $response, \Symfony\Component\Serializer\SerializerInterface $serializer, ?string $contentType = null)
    {
        if (200 === $status && DockerRawStream::HEADER === $contentType) {
            $stream = Stream::create($body);
            $stream->rewind();

            return new AttachWebsocketStream($stream);
        }

        return parent::transformResponseBody($body, $status, $serializer, $contentType);
    }
}

<?php

declare(strict_types=1);

namespace Docker\Endpoint;

use Docker\API\Endpoint\ImageBuild as BaseEndpoint;
use Docker\Stream\BuildStream;
use Docker\Stream\TarStream;
use Nyholm\Psr7\Stream;
use Symfony\Component\Serializer\SerializerInterface;

class ImageBuild extends BaseEndpoint
{
    public function getBody(SerializerInterface $serializer, $streamFactory = null): array
    {
        $body = $this->body;

        if (\is_resource($body)) {
            $body = new TarStream(Stream::create($body));
        }

        return [['Content-Type' => ['application/octet-stream']], $body];
    }

    protected function transformResponseBody(\Psr\Http\Message\ResponseInterface $response, \Symfony\Component\Serializer\SerializerInterface $serializer, ?string $contentType = null)
    {
        $body   = $response->getBody();
        $status = $response->getStatusCode();

        if (200 === $status) {
            $stream = Stream::create($body);
            $stream->rewind();

            return new BuildStream($stream, $serializer);
        }

        return parent::transformResponseBody($response, $serializer, $contentType);
    }
}

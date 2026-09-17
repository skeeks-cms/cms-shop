<?php
namespace skeeks\cms\shop\gpd;

interface CatalogTransportInterface
{
    public function request(string $method, string $endpoint, array $data = []): array;
}

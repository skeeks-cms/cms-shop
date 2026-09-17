<?php
namespace skeeks\cms\shop\gpd;

/** No credentials, URLs or response bodies are included in job errors. */
final class ProtocolException extends \RuntimeException
{
    public $reason;
    public function __construct(string $reason)
    {
        $this->reason = $reason;
        parent::__construct('GPD: '.$reason);
    }
}

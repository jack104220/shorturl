<?php

namespace App\Repositories;

use App\Models\Url;

/**
 * Url Repository
 */
class UrlRepository
{
    protected $url;

    public function __construct(Url $url)
    {
        $this->url = $url;
    }

    /**
     * 儲存短網址
     *
     * @param string $hash
     * @param string $url
     * @return object
     */
    public function save($hash, $url)
    {
        $obj = new Url();
        $obj->hash = $hash;
        $obj->url = $url;
        $obj->save();
        return $obj;
    }

    /**
     * 取得URL
     *
     * @param string $hash
     * @return string
     */
    public function getUrlByHash(string $hash)
    {
        $obj = $this->url->where('hash', $hash)->firstOrFail();
        return $obj->url;
    }
}

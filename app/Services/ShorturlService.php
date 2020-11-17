<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Redis;

use App\Repositories\UrlRepository;

/**
 * 短網址服務
 */
class ShorturlService
{
    protected $charset = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    protected $urlRepo;

    public function __construct(UrlRepository $urlRepo)
    {
        $this->urlRepo = $urlRepo;
    }

    /**
     * 尋找短連結
     *
     * @param string $hash
     * @return string
     */
    public function getUrl(string $hash)
    {
        $url = Redis::get($hash);
        if (empty($url)) {
            $url = $this->urlRepo->getUrlByHash($hash);
        }
        return $url;
    }

    /**
     * 產生短連結替代編碼
     *
     * @param string $url
     * @return string
     */
    public function getHash(string $url)
    {
        do {
            $hash = $this->genRandomString();
            if (Redis::command('setnx', [$hash, $url])) {
                try {
                    //量大要改成queue處理
                    $obj = $this->urlRepo->save($hash, $url);
                } catch (Exception $e) {
                    Log::error('Url save error: ' . $e->getMessage());
                    Redis::delete($hash);
                    throw new Exception('Url save error');
                }
                break;
            }
        } while (true);

        return $hash;
    }

    /**
     * 取得隨機字串
     *
     * @param int $length
     * @return string
     */
    public function genRandomString(int $length = 6)
    {
        $randomString = '';
        $charLength = strlen($this->charset);
        while ($length-- > 0) {
            $randomString .= $this->charset[rand(0, $charLength - 1)];
        }

        return $randomString;
    }

    /**
     * 設定字串組
     *
     * @param string $charset
     * @return void
     */
    public function setCharset(string $charset):void
    {
        $this->charset = $charset;
    }
}

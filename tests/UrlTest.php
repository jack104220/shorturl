<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Redis;

use App\Services\ShorturlService;
use App\Repositories\UrlRepository;

class UrlTest extends TestCase
{
    private static $hash;
    private $url = 'https://google.com';
    
    /**
     * 空資料測試
     *
     * @return void
     */
    public function testCreateEmpty()
    {
        $this->post('/shorturl');

        $this->seeStatusCode(200);
        $result = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('url', $result);
        $this->assertContains(
            'The url field is required.',
            $result['url'],
            '訊息錯誤'
        );
    }

    /**
     * 錯誤網址測試
     *
     * @return void
     */
    public function testCreateFormatError()
    {
        $params = ['url' => 'abc'];
        $this->post('/shorturl', $params);

        $this->seeStatusCode(200);
        $result = json_decode($this->response->getContent(), true);

        $this->assertArrayHasKey('url', $result);
        $this->assertContains(
            'The url format is invalid.',
            $result['url'],
            '訊息錯誤'
        );
    }

    /**
     * 建立成功
     *
     * @return void
     */
    public function testCreatSuccess()
    {
        $params = ['url' => $this->url];
        $this->post('/shorturl', $params);

        $this->seeStatusCode(200);
        $result = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('url', $result, '回傳無url欄位');

        $array = explode('/', $result['url']);
        $hash = $array[count($array) - 1];
        $this->assertEquals(6, strlen($hash), 'hash長度錯誤');
        static::$hash = $hash;
    }

    /**
     * 重導成功
     *
     * @depends testCreatSuccess
     * @return void
     */
    public function testShowSuccess()
    {
        $this->get('/shorturl/' . static::$hash);
        
        $this->seeStatusCode(200);
        $result = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('url', $result, '回傳無url欄位');

        $this->assertEquals($this->url, $result['url'], '連結錯誤');
    }

    /**
     * 測試service取得hash
     *
     * @group service
     * @return void
     */
    public function testServiceGetHash()
    {
        $observer = $this->getMockBuilder(UrlRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();

        $observer->expects($this->once())
            ->method('save');

        Redis::shouldReceive('command')
            ->once()
            ->andReturn(1);
        
        $obj = new ShorturlService($observer);
        $obj->getHash($this->url);
    }

    /**
     * 測試service取得Url
     *
     * @group service
     * @return void
     */
    public function testServiceGetUrl()
    {
        $observer = $this->getMockBuilder(UrlRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUrlByHash'])
            ->getMock();

        $observer->expects($this->once())
            ->method('getUrlByHash')
            ->with($this->equalTo('aaaaaa'))
            ->will($this->returnValue($this->url));

        Redis::shouldReceive('get')
            ->once()
            ->andReturn(null);
        
        $obj = new ShorturlService($observer);
        $url = $obj->getUrl('aaaaaa');
        $this->assertEquals($this->url, $url);
    }
}

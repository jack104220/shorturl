# ShortUrl 短網址實作

簡單實做短網址，沒有queue操作

## 需求

Redis, MySql

## 安裝步驟

1. composer install
2. 修改.env
3. 執行 php artisan migrate
4. 完成

## 創建短連結

POST: /shorturl
Params: url 網址
Return: 完整短連結

## 取得URL

GET: /shorturl/[hash]
Return: 原網址
<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Services\ShorturlService;

class UrlController extends Controller
{
    protected $shorturlService;

    public function __construct(ShorturlService $shorturlService)
    {
        $this->shorturlService = $shorturlService;
    }

    /**
     * 建立短連結
     *
     * @param Request $request
     * @return string
     */
    public function create(Request $request)
    {
        //驗證url
        $validator = Validator::make($request->all(), [
            'url' => 'required|url'
        ]);
        if ($validator->fails()) {
            return $validator->errors();
        }

        $url = $request->input('url');
        try {
            $hash = $this->shorturlService->getHash($url);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
        return response()->json(['url' => route('redirect', ['hash' => $hash])]);
    }

    /**
     * 導向目標連結
     *
     * @param string $hash
     * @return void
     */
    public function show($hash)
    {
        try {
            $url = $this->shorturlService->getUrl($hash);
        } catch (Exception $e) {
            return response()->json(['error' => 'Url not found']);
        }
        // return redirect()->to($url); //直接跳轉
        return response()->json(['url' => $url]);
    }
}

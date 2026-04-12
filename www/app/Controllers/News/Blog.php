<?php

namespace App\Controllers\News;

use App\Controllers\BaseController;

class Blog extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Blog',
            'content' => 'Hello'
        ];
        //return view('blog_view', $data,['cache' => 60]);//retorna el chtml
        return view('blog_view', $data);
    }
    public function showBlog(int $id, int $page): string{
        $data = [
            'title' => 'Blog',
            'content' => 'Hello',
            'id' => $id,
            'page' => $page
        ];
        //return view('blog_view', $data,['cache' => 60]);//retorna el chtml
        return view('blog_view', $data);


    }
}
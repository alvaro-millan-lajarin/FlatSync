<?php

namespace App\Controllers;

class LanguageController extends BaseController
{
    public function switch(string $locale)
    {
        $allowed = ['es', 'en', 'ca'];
        if (in_array($locale, $allowed)) {
            session()->set('lang', $locale);
        }
        return redirect()->back();
    }
}

<?php

class DocsController extends BaseController {

    /*
    |--------------------------------------------------------------------------
    | Default Home Controller
    |--------------------------------------------------------------------------
    |
    | You may wish to use controllers instead of, or in addition to, Closure
    | based routes. That's great! Here is an example controller method to
    | get you started. To route to this controller, just add the route:
    |
    |	Route::get('/', 'HomeController@showWelcome');
    |
    */

    public function getIndex() {
        return View::make('docs.index');
    }

    public function getAbout() {
        return View::make('docs.about');
    }

    public function getApps() {
        return View::make('docs.apps');
    }

    public function getPrivacy() {
        return View::make('docs.privacy');
    }

    public function getTerms() {
        return View::make('docs.terms');
    }

    public function getLegal() {
        return View::make('docs.legal');
    }

    public function getApi() {
        $endpoint = 'api.stor.ag/v1';

        return View::make('docs.api', array('endpoint' => $endpoint));
    }

}

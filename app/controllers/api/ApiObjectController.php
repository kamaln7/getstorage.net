<?php

class ApiObjectController extends BaseController {

    public function __construct() {
        $this->beforeFilter('api');
    }

    /**
     * Get all objects from the user
     *
     * @return Response
     */
    public function index() {
        //
    }

    /**
     * Store an object
     *
     * @return Response
     */
    public function store() {
        $postBody = true;
        $original = $extension = null;

        // if not Input::file it might be file_get_contents('php://input')
        if(Input::hasFile('file')) {
            $file = file_get_contents(Input::file('file')->getRealPath());
            $mime = Input::file('file')->getMimeType();

            $original = Input::file('file')->getClientOriginalName();
            $extension = Input::file('file')->getClientOriginalExtension();
        } else {
            $postBody = true;

            // Must be a post body or error
            $file = file_get_contents('php://input');
            $mime = (Request::header('Content-Type') ? Request::header('Content-Type') : 'application/octet-stream');

            if(Input::get('filename')) {
                $filename = Input::get('filename');

                $original = $filename;
                $extension = (explode('.', $filename)[1] ? explode('.', $filename)[1] : null);
            }

            if(!$file) {
                App::abort(400, 'Didn\'t send a proper file.');
            }
        }

        $newName = self::generateName();
        $filePath = self::storeFile($file, $newName);

        $apikey = Input::get('key');
        $return = Input::get('return', 'json');

        $validator = Validator::make(array('key' => $apikey), array('key' => 'required'));

        if($validator->fails()) {
            App::abort(401);
        }

        // @todo validation

        // Get the data we'll need
        $user = Sentry::getUserProvider()->findById(Key::where('key', $apikey)->first()->user_id);
        $file = new SplFileInfo($filePath);
        $path = $file;
        $fileInfo = $file->getFileInfo();

        $object = new Object();

        $object->name = $newName;
        $object->user_id = $user->id;
        $object->public = true;
        $object->mime = $mime;
        $object->original = $original;
        $object->extension = $extension;
        $object->size = $file->getSize();
        $object->path = $path;
        $object->file = json_encode(self::fileInfo($fileInfo));

        $object->save();

        // @todo do this better
        if(App::environment() != 'local') {
            Queue::push('UploadFile', array('name' => $newName));
        }

        if($return === 'json') {
            return Response::json(array('id' => $newName));
        } elseif($return === 'text') {
            echo 'http://stor.ag/e/' . $newName;
        }
    }

    /**
     * Gets the info about the file IF key owns it
     *
     * @param  int $id
     * @return Response
     */
    public function show($id) {
        $key = Input::get('key');

        $user = Sentry::getUserProvider()->findById(Key::where('key', $key)->first()->user_id);
        $object = Object::where('name', $id)->first();

        if($user->id === $object->user_id) {
            return Response::json($object);
        } else {
            return Response::json(array('error' => 'You don\'t own this!'), 401);
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id) {
        //
    }

    private function storeFile($data, $name) {
        $path = base_path() . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . $name;
        file_put_contents($path, $data);

        return $path;
    }

    /**
     * @return string
     */
    private function generateName() {
        $num = mt_rand(0, 0xffffff);
        $output = sprintf("%06x", $num);

        if(Object::where('name', $output)->count() != 0
        ) {
            return self::generateName();
        } else {
            return $output;
        }
    }

    /**
     * Returns an array of file info
     *
     * @param SplFileInfo $file
     *
     * @return array
     */
    private function fileInfo(SplFileInfo $file) {
        return array('extension' => $file->getExtension(), 'filename' => $file->getFilename(), 'size' => $file->getSize(), 'type' => $file->getType());
    }

}

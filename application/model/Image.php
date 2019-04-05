<?php


namespace App\model;


use App\Lib\Resize;
use App\System\Model;
use function GuzzleHttp\Psr7\copy_to_stream;

class Image extends Model
{
    private $image_types = [IMAGETYPE_JPEG, IMAGETYPE_PNG];
    public function resize($file_name, $width, $height) {
        $extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $image_old = $file_name;
        $image_new = 'cache/' . substr($file_name, 0, strpos($file_name, '.')) . '-' . $width . 'x' . $height . '.' . $extension;
        if(!is_file(ASSETS_PATH . DS . $image_new) || (filemtime(ASSETS_PATH . DS . $image_new) < filemtime(ASSETS_PATH . DS . $image_old))) {
            list($width_original, $height_original, $image_type, $image_mime) = getimagesize(ASSETS_PATH . DS . $image_old);
            if(!in_array($image_type, $this->image_types)) {
                return $image_old;
            }
            $directories = explode('/', dirname($image_new));
            $path = '';
            foreach ($directories as $directory) {
                $path .= '/' . $directory;
                if(!is_dir(ASSETS_PATH . $path)) {
                    @mkdir(ASSETS_PATH . $path, 0777);
                }
            }
            if($width != $width_original || $height != $height_original) {
                $Resize = new Resize(ASSETS_PATH . DS . $image_old);
                $Resize->resizeToBestFit($width, $height);
                $Resize->save(ASSETS_PATH . DS . $image_new);
            }else {
                copy(ASSETS_PATH . DS . $image_old, ASSETS_PATH . DS . $image_new);
            }

        }
        return $image_new;
    }

}
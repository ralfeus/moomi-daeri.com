<?php
class ModelToolImage extends Model {
    public function download($url)
    {
        if (preg_match('/https?:\/\/([\w\-\.]+)/', $url))
        {
            $fileName = $this->getImageFileName($url);
            if ($fileName)
            {
                $dirName = DIR_IMAGE . 'upload/' . session_id();
                if (!file_exists($dirName))
                    mkdir($dirName);
                file_put_contents($dirName . '/' . $fileName, file_get_contents($url));
                return 'upload/' . session_id() . '/' . $fileName;
            }
            else
                throw new HttpRequestException("Provided URL isn't image");
        }
        else
            throw new HttpRequestException("Provided URL isn't image");
    }

    private function getImageFileName($fileName)
    {
        if (@exif_imagetype($fileName))
            return sprintf("%f6", microtime(true)) . image_type_to_extension(exif_imagetype($fileName));
        else
            return '';
    }

    public function getImage($imagePath)
    {
        if ($imagePath && file_exists(DIR_IMAGE . $imagePath)):
            return $this->resize($imagePath, 100, 100);
        else:
            return $this->resize('no_image.jpg', 100, 100);
        endif;
    }

	public function resize($filename, $width, $height) {
		if (!file_exists(DIR_IMAGE . $filename) || !is_file(DIR_IMAGE . $filename)) {
			return;
		}

		$info = pathinfo($filename);
		$extension = $info['extension'];

		$old_image = $filename;
		$new_image = 'cache/' . utf8_substr($filename, 0, strrpos($filename, '.')) . '-' . $width . 'x' . $height . '.' . $extension;

		if (!file_exists(DIR_IMAGE . $new_image) || (filemtime(DIR_IMAGE . $old_image) > filemtime(DIR_IMAGE . $new_image))) {
			$path = '';

			$directories = explode('/', dirname(str_replace('../', '', $new_image)));

			foreach ($directories as $directory) {
				$path = $path . '/' . $directory;

				if (!file_exists(DIR_IMAGE . $path)) {
					@mkdir(DIR_IMAGE . $path, 0777);
				}
			}
			try
      {
        $image = new Image(DIR_IMAGE . $old_image);
      }
      catch (Exception $exc)
      {
        $this->log->write("The file $old_image has wrong format and can not be handled.");
        $image = new Image(DIR_IMAGE . 'no_image.jpg');
      }
      $image->resize($width, $height);
      $image->save(DIR_IMAGE . $new_image);
    }

		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			return HTTPS_IMAGE . $new_image;
		} else {
			return HTTP_IMAGE . $new_image;
		}
	}
}
?>
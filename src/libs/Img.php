<?php

namespace Vis\Builder;

use Intervention\Image\Facades\Image;

class Img
{
    private $size;
    private $nameFile;
    private $picturePath;
    private $pathFolder;
    private $width = null;
    private $height = null;
    private $quality = 80;

    public function get($source, $options)
    {
        if (! $source) {
            return;
        }

        $this->setOptions($options);
        $source = '/'.ltrim($source, '/');
        $sourceArray = pathinfo($source);

        //create variables $dirname , $basename, $extension, $filename
        extract($sourceArray);
        if ($this->quality != 80) {
            $this->nameFile = $filename.'_'.$this->quality.'.'.$extension;
        } else {
            $this->nameFile = $filename.'.'.$extension;
        }

        $this->pathFolder = $dirname.'/'.$this->size;
        $this->picturePath = $this->pathFolder.'/'.$this->nameFile;

        if ($extension == 'svg') {
            return $source;
        }

        if (self::checkExistPicture()) {
            return $this->picturePath;
        }

        try {
            $img = Image::make(public_path().$source);

            $this->createRatioImg($img, $options);

            @mkdir(public_path().$this->pathFolder);

            $pathSmallImg = public_path().'/'.$this->picturePath;
            $img->save($pathSmallImg, $this->quality);

            OptmizationImg::run($this->picturePath);

            return  $this->picturePath;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    protected function setOptions($options)
    {
        $this->quality = $options['quality'] ?? $this->quality;
        $this->height = $options['h'] ?? $this->height;
        $this->width = $options['w'] ?? $this->width;

        if ($this->height === null) {
            $this->size = $this->width.'x0';
        } elseif ($this->width === null) {
            $this->size = '0x'.$this->height;
        } else {
            $this->size = $this->width.'x'.$this->height;
        }
    }

    protected function createRatioImg($img, $options)
    {
        if (isset($options['fit']) && $options['fit'] == 'crop') {
            $img->fit(
                $this->width,
                $this->height,
                function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                }
            );
        } else {
            $img->resize(
                $this->width,
                $this->height,
                function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                }
            );
        }
    }

    protected function checkExistPicture()
    {
        return file_exists(public_path().$this->picturePath);
    }
}

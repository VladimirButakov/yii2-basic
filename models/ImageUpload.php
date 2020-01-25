<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class ImageUpload extends Model
{
    public $image;


    public function rules() {

        return [
            [['image'], 'required'],
            [['image'], 'file', 'extensions' => 'jpg,png'],
        ];
    }

    /**
     * Метод загрузки изображения.
     * @param UploadedFile $file
     * @param string $currentimage
     * @return string
     */
    public function uploadFile(UploadedFile $file, $currentimage ) : string 
    {
        $this->deleteCurrentImage($currentimage);
        $this->image = $file;
        $filename = $this->generateFileName();
        $file->saveAs($this->getFolder() . $filename);

        return (string) $filename;
    }

    /**
     * Возвращает путь к хранилищу
     */
    private function getFolder()
    {
        return Yii::getAlias('@web') . 'uploads/';
    }

    /**
     * Создает название файла
     * 
     * @return string
     */
    private function generateFileName() : string
    {
        return (string) strtolower(md5(uniqid($this->image->basename)) . '.' . $this->image->extension);
    }

    public function deleteCurrentImage($currentimage)
    {
        $folder = $this->getFolder();

        if (is_file($folder . $currentimage)) 
        {
            unlink($folder . $currentimage);
        }
    }
}
<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function uploadImage($path, $folderPath = 'uploads'){
        $imagePath = md5(time() . $path->getFilename()) . '.' . $path->getClientOriginalExtension();
        return  $path->storeAs($folderPath, $imagePath, 'public');
    }
    public function deleteImage($path){
        $fullpath = storage_path('app/public/' . $path);
        if(file_exists($fullpath)){
            unlink($fullpath);
        }
    }
}
//Polymorphic Relation image upload
//Image model bo'ladi polymorphic bo'lsin
//Image model ichida url() metod bo'lsin
//Auth - yozing
//register - name,email,avatar va password
//login - email va password
//Avatar images tableda saqlansin User va Image one to one polymorphic relation
//Auth qilgach Book CRUD qilsin
//Book(title,description, author_id) -  Image bilan polymorphic One to Many relation
//index() - pagination resource bilan search xam bo'lsin
//Abstract Controller ichida uploadFIle($file,$path="uploads") va deleteFile($path) metodlar bo'lsin
//Talab qilinadi yana:
//Resource-lar UserResource,BookResource va ImageResource
//with() yoki load() doyim ishlating kerak bolgan joyda
//Resoure-lar ichida whenLoaded() ishlating doyim


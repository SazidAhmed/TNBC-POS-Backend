<?php

namespace App\Services\User;

//Hash
use Illuminate\Support\Facades\Hash;

//Rules
use App\Rules\MatchOldPassword;

//Interface
use App\Contracts\ProfileRepositoryInterface;
use App\Contracts\UserRepositoryInterface;

//Utilities
use App\Utilities\FileUtilities;

class ProfileServices{
    
    private $repositoryInterface;
    private $userRepositoryInterface;
    private $fileUtilities;
    public static $imagePath = 'images/profile';
    public static $explode_at = "profile/";

    public function __construct(
        ProfileRepositoryInterface $repositoryInterface, 
        FileUtilities $fileUtilities, 
        UserRepositoryInterface $userRepositoryInterface){
        $this->ri = $repositoryInterface;
        $this->uri = $userRepositoryInterface;
        $this->fileUtilities = $fileUtilities;
    }

    public function userProfileGetById($id){
        $profile = $this->ri->userProfileFindById($id);
        if($profile){
            return $profile;
        }else{
            return response(["failed"=>'Profile not found'],404);
        }
    }

    public function userProfileCreate($request){
        $request->validate([
            'mobile'=>'required',
            'present_address'=>'required|min:3',
            'identity_number'=>'required|numeric',
            'user_id'=>'required',
        ]);

        $data = $request->all();
        $url  = url('');
        
        //image upload
        $profileImage = $this->fileUtilities->fileUpload($request, $url, self::$imagePath, self::$explode_at, false, false);
        $data['image'] = $profileImage;

        $profile = $this->ri->userProfileCreate($data);
        return response($profile,201);
    }

    public function userProfileUpdate($request, $id){
        $profile = $this->ri->userProfileFindById($id);
        if($profile){
            $request->validate([
                'mobile'=>'required',
                'present_address'=>'required|min:3',
                'identity_number'=>'required|numeric',
                'user_id'=>'required',
            ]);
            $data = $request->all();
            $exImagePath = $profile->image;
            $url  = url('');
            
            //image upload
            $profileImage = $this->fileUtilities->fileUpload($request, $url, self::$imagePath, self::$explode_at, $exImagePath, true);
            $data['image'] = $profileImage;

            $profile->update($data);
            return response($profile,201);
        }else{
            return response(["failed"=>'Profile not found'],404);
        }
    }

    public function userProfileDelete($id){
        $profile = $this->ri->userProfileFindById($id);
        if($profile){
            $exImagePath = $profile->image;
            $this->fileUtilities->removeExistingFile(self::$imagePath, $exImagePath, self::$explode_at);
            $profile->delete();
            return response(["done"=>'Profile Deleted Successfully'],200);
        }else{
            return response(["failed"=>'Profile not found'],404);
        }
    }

    public function profileSettingPhotoUpdate($request){
        $user = $this->uri->userGetByAuth();
        $profile = $user->profile;
        if($profile){
            $data = $request->all();
            $exImagePath = $profile->image;
            $url  = url('');
            
            //image upload
            $profileImage = $this->fileUtilities->fileUpload($request, $url, self::$imagePath, self::$explode_at, $exImagePath, true);
            
            $profile->image = $profileImage;
            $profile->save();

            return response(["image"=>$profileImage],201);
        }else{
            return response(["failed"=>'Profile not found'],404);
        }
    }

    public function profileSettingPasswordUpdate($request){
        $request->validate([
            'current_password' => ['required', new MatchOldPassword],
            'new_password' => ['required'],
            'new_confirm_password' => ['same:new_password'],
        ]);

        $this->uri->userGetByAuth()->update(['password'=> Hash::make($request->new_password)]);
        // auth()->user()->tokens()->delete();
        return response(["done"=>'Password changed successfully'],200);
    }
}
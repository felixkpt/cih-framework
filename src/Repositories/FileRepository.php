<?php
/**
 * Created by PhpStorm.
 * User: iankibet
 * Date: 7/25/18
 * Time: 8:13 AM
 */

namespace Cih\Framework\Repositories;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FileRepository
{
    public static function move($file, $model_id, $public = false, $path = null)
    {
        try {
            $originan_name = $file->getClientOriginalName();
            $file_type = $file->getClientMimeType();
            $file_size = $file->getSize();

            $arr = explode('.', $originan_name);
            $ext = $arr[count($arr) - 1];
            $file_name = str::slug(str_replace($ext, '', $originan_name)) . '.' . $ext;
            if ($public) {
                $pre = 'public';
            } else {
                $pre = 'app';
            }
            if ($path)
                $path = $path . '/' . $model_id . '/' . Carbon::now()->format('Y/m/d');
            else
                $path = '/staffs/' . $model_id . '/' . Carbon::now()->format('Y/m/d');

            $new_path = $path . "/";

            if (!$public) {
//                $path = storage_path() . '/app/' . $new_path;
                File::ensureDirectoryExists(storage_path() . '/app/' . $new_path);
//                dd(storage_path() . '/app/' . $new_path);
            }

            $new_name = Str::random(3) . '_' . date('H_i_s') . '_' . $file_name;
            $disk = env('FILESYSTEM_DRIVER', 'local');
            if ($public) {
                $disk = 'local';
            }

            Storage::disk($disk)->putFileAs($new_path, $file, $new_name);

            if ($public)
                $pre = 'storage';
            return [
                'file_name' => $originan_name,
                'file_size' => $file_size,
                'path' => $path . '/' . $new_name,
                'file_type' => $file_type,
                'uploaded' => true,
                'ext' => $ext,
                'disk' => $disk
            ];
        } catch (\Exception $e) {
            return [
                'uploaded' => false,
                'error' => $e->getMessage()
            ];
        }

    }

    public static function download($file)
    {
        if (file_exists($file->path))
            return \response()->download($file->path);

        if ($file->disk != 'local') {
            return Storage::disk($file->disk)->download($file->path);
        }
        if (file_exists(storage_path('app/' . $file->path)))
            return \response()->download(storage_path('app/' . $file->path));
        if (file_exists(storage_path('app/' . str_replace('public/', '', $file->path))))
            return \response()->download(storage_path('app/' . str_replace('public/', '', $file->path)));

        $slug = Str::slug($file->name);
        $file_path = storage_path('app/' . $file->path . $slug);
        $file_path = str_replace('-' . $file->type, '.' . $file->type, $file_path);
        if (file_exists($file_path))
            return \response()->download($file_path);
        $slug = str_replace($file->type, '.' . $file->type, $slug);
        $file_path = storage_path('app/' . $file->path . $slug);
        if (file_exists($file_path))
            return \response()->download($file_path);
        $file_path = storage_path('app/' . $file->path . $file->name);
        if (file_exists($file_path))
            return \response()->download($file_path);
        $file_path = storage_path('app/' . $file->path . str::slug($file->name));
        return \response()->download($file_path);
    }

    /**
     * delete file
     */
    public static function delete($file)
    {
        Storage::disk($file->disk)->delete($file->path);
        return $file->delete();
    }

    public static function moveFile($file, $model_id, $public = false, $path = null)
    {
        try {
            $originan_name = $file->getClientOriginalName();
            $file_type = $file->getClientMimeType();
            $file_size = $file->getSize();
//            dd($file_size);
            $arr = explode('.', $originan_name);
            $ext = $arr[count($arr) - 1];
            $file_name = str::slug(str_replace($ext, '', $originan_name)) . '.' . $ext;
            if ($public) {
                $pre = 'public';
            } else {
                $pre = 'app';
            }
            if ($path)
                $path = $path . '/' . $model_id . '/' . Carbon::now()->format('Y/m/d');
            else
                $path = '/staffs/' . $model_id . '/' . Carbon::now()->format('Y/m/d');

            $new_path = $path . "/";

            if (!$public) {
//                $path = storage_path() . '/app/' . $new_path;
                File::ensureDirectoryExists(storage_path() . '/app/' . $new_path);
//                dd(storage_path() . '/app/' . $new_path);
            }

            $new_name = Str::random(3) . '_' . date('H_i_s') . '_' . $file_name;
            $disk = env('FILESYSTEM_DRIVER', 'local');
            if ($public) {
                $disk = 'local';
            }

            Storage::disk($disk)->putFileAs($new_path, $file, $new_name);

            if ($public)
                $pre = 'storage';
            return [
                'file_name' => $originan_name,
                'file_size' => $file_size,
                'path' => $path . '/' . $new_name,
                'file_type' => $file_type,
                'uploaded' => true,
                'ext' => $ext,
                'disk' => $disk
            ];
        } catch (\Exception $e) {
            return [
                'uploaded' => false,
                'error' => $e->getMessage()
            ];
        }

    }

    public static function saveTmpFiles($storage_path, $temp_path, $delete_url)
    {
        if (\request('clear')) {
            $directory = storage_path($storage_path);
            $files = @scandir($directory);
            if ($files) {
                foreach ($files as $file) {
                    if (file_exists($directory . $file) && $file != '.' && $file != '..') {
                        @unlink($directory . $file);
                    }
                }
            }
            request()->session()->forget('tmp_files');
            return ['cleared' => true];
        }
        $files = request()->file('files');
        $config = [];
        $tmp_files_array = [];
        $session_array = [];
        foreach ($files as $key => $image) {
            $tmp_file_name = $image->getClientOriginalName();
            $ext = $image->getClientOriginalExtension();
            $directory = storage_path($storage_path);
            $file_name = Str::slug(microtime() . $tmp_file_name) . "." . $ext;
            $path = $directory . $file_name;
            $temp_file_arr = [
                'file_name' => $tmp_file_name,
                'file_size' => $image->getSize(),
                'file_type' => $ext,
                'key' => $key,
                'tmp_file_name' => $file_name,
                'file_path' => $path,
                'mime_type' => $image->getMimeType(),
                'path' => url($temp_path . $file_name)
            ];
            array_push($tmp_files_array, $temp_file_arr);
            array_push($session_array, $temp_file_arr);
            $file_type = self::getFileType($image->getMimeType());
            $config_data = [
                'key' => $key,
                'width' => '120px',
                'caption' => $tmp_file_name,
                'type' => $file_type,
                'previewAsData' => true,
                'size' => $image->getSize(),
                'url' => url($delete_url) . '?_token=' . csrf_token(),//delete url
                'path' => url($temp_path . '/' . $file_name)
            ];
            array_push($config, $config_data);
            if ($file_type == "image") {
                $preview[] = url($temp_path . $file_name);
            } else {
                $preview[] = url($temp_path . $file_name);
            }
            try {
                if (\Illuminate\Support\Facades\Storage::exists($path)) {
                } else {
                    $image->move($directory, $file_name);
                }

            } catch (\Exception $e) {
                $error = $e->getMessage();
                return [
                    'error' => $error,
                    'initialPreviewConfig' => $config,
                    'initialPreviewAsData' => true
                ];
            }
        }
        foreach ($session_array as $ses_array) {
            Session::push('tmp_files', $ses_array);
        }

        $image_data = [
            'success' => true,
            'images' => request()->session()->get('tmp_files'),
            'initialPreview' => $preview,
            'initialPreviewAsData' => true,
            'allowedPreviewTypes' => ['image', 'pdf'],
            'initialPreviewConfig' => $config,
            'previewFileIconSettings' => [
                'docx' => '<i class="fas fa-file-word text-primary"></i>',
                'xlsx' => '<i class="fas fa-file-excel text-success"></i>',
                'pptx' => '<i class="fas fa-file-powerpoint text-danger"></i>',
                'jpg' => '<i class="fas fa-file-image text-warning"></i>',
                'pdf' => '<i class="fas fa-file-pdf text-danger"></i>',
                'zip' => '<i class="fas fa-file-archive text-muted"></i>',
            ]
        ];
        return $image_data;

    }

    public function saveTmpFilesBack()
    {
        if (\request('clear')) {
            $directory = storage_path("app/public/trucks/tmp/" . \request()->user()->id . '/');
            $files = @scandir($directory);
            if ($files) {
                foreach ($files as $file) {
                    if (file_exists($directory . $file) && $file != '.' && $file != '..') {
                        @unlink($directory . $file);
                    }
                }
            }

            \request()->session()->forget('tmp_files');
            return ['cleared' => true];
        }

        $files = \request()->images;
        $config = [];
        $tmp_files_array = [];
        $session_array = [];
        foreach ($files as $key => $image) {
            $tmp_file_name = $image->getClientOriginalName();
            $ext = $image->getClientOriginalExtension();
            $directory = storage_path("app/public/trucks/tmp/" . \request()->user()->id . '/');
            $file_name = Str::slug(microtime() . $tmp_file_name) . "." . $ext;
            $path = $directory . $file_name;
            $temp_file_arr = [
                'file_name' => $tmp_file_name,
                'file_size' => $image->getSize(),
                'file_type' => $ext,
                'key' => $key,
                'tmp_file_name' => $file_name,
                'file_path' => $path,
                'path' => url('storage/trucks/tmp/' . \request()->user()->id . '/' . $file_name)
            ];
            array_push($tmp_files_array, $temp_file_arr);
            array_push($session_array, $temp_file_arr);
            $config_data = [
                'key' => $key,
                'width' => '120px',
                'caption' => $tmp_file_name,
                'size' => $image->getSize(),
                'url' => url("admin/truckentry/tmp/delete") . '?_token=' . csrf_token(), // server api to delete the file based on key
            ];
            array_push($config, $config_data);
            $preview[] = url('storage/trucks/tmp/' . \request()->user()->id . '/' . $file_name);

            try {

                if (Storage::exists($path)) {

                } else {
                    $image->move($directory, $file_name);
                }

            } catch (\Exception $e) {
                $error = $e->getMessage();
                return [
                    'error' => $error,
                    'initialPreviewConfig' => $config,
                    'initialPreviewAsData' => true
                ];
            }
        }
        foreach ($session_array as $ses_array) {
            Session::push('tmp_files', $ses_array);
        }

        return [
            'success' => true,
            'images' => \request()->session()->get('tmp_files'),
            'initialPreview' => $preview,
            'initialPreviewConfig' => $config,
            'initialPreviewAsData' => true
        ];

    }

    public static function getFileType($mimeType)
    {

        $allowedMimeTypes = ['image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/svg+xml'];

        if ($mimeType == "application/pdf") {
            $file = "pdf";
        } elseif ($mimeType == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
            $file = "office";
        } elseif ($mimeType == "text/plain") {
            $file = "text";
        } elseif ($mimeType == "application/octet-stream") {
            $file = "office";
        } elseif ($mimeType == "application/msword") {
            $file = "office";
        } elseif (!in_array($mimeType, $allowedMimeTypes)) {
            $file = "image";
        } else {
            $file = "image";
        }
        return $file;

    }

}

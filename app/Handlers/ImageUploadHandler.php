<?php

namespace App\Handlers;

use Image;

class ImageUploadHandler
{
	protected $allowed_ext = ['png', 'jpg', 'gif', 'jpeg'];

	public function save($file, $folder, $file_prefix, $max_width)
	{
		// 拼接保存文件的物理路径
		$folder_name = "uploads/images/$folder/".date('Ym/d');
		$upload_path = public_path().'/'.$folder_name;

		// 获取扩展名
		$extension = strtolower($file->getClientOriginalExtension()) ?: 'png';

		// 获取新文件名
		$filename = $file_prefix.'_'.time().'_'.str_random(10).'.'.$extension;

		// 判断扩展名
		if (!in_array($extension, $this->allowed_ext)) {
			return false;
		}

		// 将图片移动到目标存储路径中
		$file->move($upload_path, $filename);


		// 判断是否要裁剪
		if ($max_width && $extension != 'gif') {
			$this->reduceSize($upload_path.'/'.$filename, $max_width);
		}

		return [
			'path' => config('app.url')."/$folder_name/$filename",
		];
	}

	public function reduceSize($file_path, $max_width)
	{
		// 实例化,参数为图片文件的磁盘物理路径
		$image = Image::make($file_path);

		// 进行大小调整的操作
		$image->resize($max_width, null, function($constraint){
			// 设定宽度是 $max_width, 高度等比例双方缩放
			$constraint->aspectRatio();

			// 防止裁图时图片尺寸变大
			$constraint->upsize();
		});

		// 对图片修改后进行保存
		$image->save();
	}
}
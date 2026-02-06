<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Spatie\Image\Image;

class CropImages extends Command
{
    protected $signature = 'images:crop {source_folder} {destination_folder} {crop_height=100}';
    protected $description = 'Crop images from the source folder and save them to the destination folder';

    public function handle()
    {
        $sourceFolder = $this->argument('source_folder');
        $destinationFolder = $this->argument('destination_folder');
        $cropHeight = (int) $this->argument('crop_height');

        if (!File::isDirectory($sourceFolder)) {
            $this->error("Source folder does not exist: $sourceFolder");
            return;
        }

        if (!File::isDirectory($destinationFolder)) {
            File::makeDirectory($destinationFolder, 0755, true);
        }

        $files = File::files($sourceFolder);
        $this->info("Found " . count($files) . " images to crop.");

        foreach ($files as $file) {
            $extension = strtolower($file->getExtension());
            if (in_array($extension, ['jpeg', 'jpg', 'png', 'gif'])) {
                $this->info("Processing: " . $file->getFilename());

                $image = Image::load($file->getRealPath());

                // Crop from the bottom
                $image->crop(0, 0, 0, $cropHeight);

                // Save the cropped image to the destination folder
                $destinationPath = $destinationFolder . '/' . $file->getFilename();
                $image->save($destinationPath);

                $this->info("Cropped and saved: " . $file->getFilename());
            }
        }

        $this->info('Image cropping process completed.');
    }
}

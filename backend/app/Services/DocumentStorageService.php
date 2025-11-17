<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class DocumentStorageService
{
    protected string $disk;
    protected string $basePath;
    protected ?ImageManager $imageManager = null;
    
    public function __construct()
    {
        $this->disk = config('filesystems.default');
        $this->basePath = 'merchant-documents';
    }
    
    public function store(UploadedFile $file, string $documentType, ?string $referencePrefix = null): array
    {
        $this->validateFile($file);
        
        $filename = $this->generateSecureFilename($file, $documentType, $referencePrefix);
        $path = $this->basePath . '/' . $documentType . '/' . date('Y/m');
        $fullPath = $path . '/' . $filename;
        
        if ($this->isImage($file)) {
            $processedFile = $this->processImage($file);
            Storage::disk($this->disk)->put($fullPath, $processedFile);
        } else {
            Storage::disk($this->disk)->putFileAs($path, $file, $filename);
        }
        
        $hash = hash_file('sha256', Storage::disk($this->disk)->path($fullPath));
        
        return [
            'filename' => $filename,
            'path' => $fullPath,
            'size' => $file->getSize(),
            'type' => $file->getMimeType(),
            'hash' => $hash
        ];
    }
    
    protected function validateFile(UploadedFile $file): void
    {
        $realExtension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();
        
        $allowedTypes = [
            'jpg' => ['image/jpeg', 'image/jpg'],
            'jpeg' => ['image/jpeg', 'image/jpg'],
            'png' => ['image/png'],
            'pdf' => ['application/pdf'],
        ];
        
        if (!isset($allowedTypes[$realExtension]) || 
            !in_array($mimeType, $allowedTypes[$realExtension])) {
            throw new \InvalidArgumentException('Type de fichier non autorisé');
        }
        
        if ($file->getSize() > 5 * 1024 * 1024) {
            throw new \InvalidArgumentException('Fichier trop volumineux (5MB max)');
        }
    }
    
    protected function generateSecureFilename(UploadedFile $file, string $documentType, ?string $referencePrefix = null): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('YmdHis');
        $random = Str::random(12);
        
        // Si un préfixe de référence est fourni, l'ajouter au début du nom de fichier
        if ($referencePrefix) {
            return "{$referencePrefix}_{$documentType}_{$timestamp}_{$random}.{$extension}";
        }
        
        return "{$documentType}_{$timestamp}_{$random}.{$extension}";
    }
    
    protected function processImage(UploadedFile $file): string
    {
        // Check if image processing extensions are available
        if (!extension_loaded('gd') && !extension_loaded('imagick')) {
            // If no image processing extension is available, return the file content as-is
            return file_get_contents($file->path());
        }
        
        try {
            // Initialize ImageManager only when needed
            if ($this->imageManager === null) {
                $this->imageManager = new ImageManager(new Driver());
            }
            
            $image = $this->imageManager->read($file->path());
            
            // Redimensionnement
            if ($image->width() > 1200 || $image->height() > 1200) {
                $image->scale(width: 1200, height: 1200);
            }
            
            // Compression
            $quality = $this->calculateOptimalQuality($file->getSize());
            
            return $image->toJpeg($quality)->toString();
        } catch (Exception $e) {
            // If image processing fails, return the original file content
            return file_get_contents($file->path());
        }
    }
    
    protected function calculateOptimalQuality(int $filesize): int
    {
        if ($filesize > 2 * 1024 * 1024) return 70;
        if ($filesize > 1 * 1024 * 1024) return 80;
        return 85;
    }
    
    protected function isImage(UploadedFile $file): bool
    {
        return str_starts_with($file->getMimeType(), 'image/');
    }
    
    public function delete(string $filePath): bool
    {
        return Storage::disk($this->disk)->delete($filePath);
    }
}

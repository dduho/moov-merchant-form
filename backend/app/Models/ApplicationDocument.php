<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class ApplicationDocument extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'merchant_application_id', 'document_type', 'original_name',
        'file_name', 'file_path', 'mime_type', 'file_size',
        'upload_ip', 'hash_sha256', 'hash_md5', 'description',
        'is_verified', 'verified_at', 'verified_by', 'verification_notes',
        'metadata'
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function merchantApplication(): BelongsTo
    {
        return $this->belongsTo(MerchantApplication::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn () => url('/documents/' . str_replace('merchant-documents/', '', $this->file_path))
        );
    }

    protected function formattedSize(): Attribute
    {
        return Attribute::make(
            get: function () {
                $bytes = $this->file_size;
                $units = ['B', 'KB', 'MB', 'GB'];
                for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
                    $bytes /= 1024;
                }
                return round($bytes, 2) . ' ' . $units[$i];
            }
        );
    }

    public function deleteFile(): bool
    {
        return Storage::delete($this->file_path);
    }

    public function verifyIntegrity(): bool
    {
        if (!$this->hash_sha256) return true;

        $currentHash = hash_file('sha256', Storage::path($this->file_path));
        return $currentHash === $this->hash_sha256;
    }

    public function getTypeLabel(): string
    {
        $labels = [
            'id_card' => "Pièce d'identité",
            'anid_card' => "Carte ANID", 
            'cfe_document' => "Document CFE",
            'business_document' => "Document commercial",
            'residence_card' => "Carte de séjour",
            'residence_proof' => "Justificatif de résidence",
            'nif_document' => "Document NIF",
        ];
        
        return $labels[$this->document_type] ?? ucfirst(str_replace('_', ' ', $this->document_type));
    }
}

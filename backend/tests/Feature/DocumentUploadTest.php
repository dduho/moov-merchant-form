<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentUploadTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_can_upload_document(): void
    {
        Storage::fake('local');
        
        $file = UploadedFile::fake()->image('document.jpg', 800, 600);
        
        $response = $this->postJson('/api/documents/upload', [
            'file' => $file,
            'type' => 'id_card',
            'description' => 'Test document'
        ]);
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'data' => ['id', 'original_name', 'file_size']
                 ]);
        
        $this->assertDatabaseHas('application_documents', [
            'document_type' => 'id_card',
            'original_name' => 'document.jpg'
        ]);
    }
    
    public function test_rejects_large_files(): void
    {
        Storage::fake('local');
        
        $file = UploadedFile::fake()->create('large.jpg', 6000); // 6MB
        
        $response = $this->postJson('/api/documents/upload', [
            'file' => $file,
            'type' => 'id_card'
        ]);
        
        $response->assertStatus(422);
    }
    
    public function test_rejects_invalid_file_types(): void
    {
        Storage::fake('local');
        
        $file = UploadedFile::fake()->create('document.txt', 100);
        
        $response = $this->postJson('/api/documents/upload', [
            'file' => $file,
            'type' => 'id_card'
        ]);
        
        $response->assertStatus(422);
    }
}
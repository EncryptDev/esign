<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\SignatureArea;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->info('No users found. Please run UserSeeder first.');
            return;
        }

        foreach ($users->take(3) as $user) {
            // Create draft documents
            for ($i = 1; $i <= 2; $i++) {
                $document = Document::create([
                    'user_id' => $user->id,
                    'title' => "Contract Document {$i} - {$user->name}",
                    'description' => "This is a sample contract document for testing purposes.",
                    'purpose' => 'Business Agreement',
                    'original_filename' => "contract_{$i}.pdf",
                    'mime_type' => 'application/pdf',
                    'file_size' => rand(100000, 500000),
                    'storage_path' => "documents/{$user->uuid}/" . Str::uuid() . "/original.pdf",
                    'status' => 'draft',
                    'page_count' => rand(1, 5),
                    'checksum' => hash('sha256', Str::random(40)),
                ]);

                // Create original version
                DocumentVersion::create([
                    'document_id' => $document->id,
                    'version_number' => 1,
                    'version_type' => 'original',
                    'storage_path' => $document->storage_path,
                    'file_size' => $document->file_size,
                    'checksum' => $document->checksum,
                    'metadata' => [
                        'page_count' => $document->page_count,
                        'upload_date' => now()->toDateString(),
                    ],
                    'created_by' => $user->id,
                ]);

                // Add signature areas for draft documents
                for ($page = 1; $page <= min(2, $document->page_count); $page++) {
                    SignatureArea::create([
                        'document_id' => $document->id,
                        'page_number' => $page,
                        'position_x' => rand(50, 150),
                        'position_y' => rand(50, 150),
                        'width' => 150,
                        'height' => 80,
                        'label' => "Signature Area {$page}",
                        'is_signed' => false,
                    ]);
                }
            }

            // Create one signed document
            $signedDoc = Document::create([
                'user_id' => $user->id,
                'title' => "Signed Agreement - {$user->name}",
                'description' => "This document has been digitally signed.",
                'purpose' => 'Employment Contract',
                'original_filename' => 'employment_contract.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => rand(100000, 500000),
                'storage_path' => "documents/{$user->uuid}/" . Str::uuid() . "/original.pdf",
                'status' => 'signed',
                'page_count' => 3,
                'checksum' => hash('sha256', Str::random(40)),
            ]);

            // Create versions for signed document
            DocumentVersion::create([
                'document_id' => $signedDoc->id,
                'version_number' => 1,
                'version_type' => 'original',
                'storage_path' => $signedDoc->storage_path,
                'file_size' => $signedDoc->file_size,
                'checksum' => $signedDoc->checksum,
                'metadata' => ['page_count' => $signedDoc->page_count],
                'created_by' => $user->id,
            ]);

            DocumentVersion::create([
                'document_id' => $signedDoc->id,
                'version_number' => 2,
                'version_type' => 'signed',
                'storage_path' => str_replace('original.pdf', 'signed.pdf', $signedDoc->storage_path),
                'file_size' => $signedDoc->file_size + 50000,
                'checksum' => hash('sha256', Str::random(40)),
                'metadata' => ['page_count' => $signedDoc->page_count],
                'created_by' => $user->id,
            ]);

            // Add signed signature area
            SignatureArea::create([
                'document_id' => $signedDoc->id,
                'page_number' => 1,
                'position_x' => 100,
                'position_y' => 100,
                'width' => 150,
                'height' => 80,
                'label' => 'Primary Signature',
                'is_signed' => true,
            ]);
        }

        $this->command->info('Documents seeded successfully!');
    }
}

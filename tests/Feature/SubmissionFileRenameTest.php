<?php

namespace Tests\Feature;

use App\Models\Submission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubmissionFileRenameTest extends TestCase
{
    use DatabaseTransactions;

    public function test_manuscript_file_is_renamed_following_submission_id_on_create(): void
    {
        Storage::fake('public');

        // Create a temporary uploaded file
        $file = UploadedFile::fake()->create('original_document.pdf', 100, 'application/pdf');
        
        // Store it initially as Filament would
        $storedPath = Storage::disk('public')->putFileAs('manuscripts', $file, 'original_document.pdf');
        
        $this->assertEquals('manuscripts/original_document.pdf', $storedPath);
        $this->assertTrue(Storage::disk('public')->exists('manuscripts/original_document.pdf'));

        // Create the submission model
        $submission = Submission::create([
            'author_name' => 'Test Author',
            'title' => 'Test Title',
            'email' => 'test@example.com',
            'manuscript_file' => 'manuscripts/original_document.pdf',
        ]);

        $submission->refresh();

        // Check file has been renamed matching the ID
        $expectedPath = "manuscripts/file-{$submission->id}.pdf";
        $this->assertEquals($expectedPath, $submission->manuscript_file);
        
        // Verify original file is gone and renamed file exists
        $this->assertFalse(Storage::disk('public')->exists('manuscripts/original_document.pdf'));
        $this->assertTrue(Storage::disk('public')->exists($expectedPath));
    }

    public function test_manuscript_file_is_renamed_following_submission_id_on_update(): void
    {
        Storage::fake('public');

        // Create submission without a file initially
        $submission = Submission::create([
            'author_name' => 'Test Author',
            'title' => 'Test Title',
            'email' => 'test@example.com',
        ]);

        // Create a temporary uploaded file for update
        $file = UploadedFile::fake()->create('updated_document.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $storedPath = Storage::disk('public')->putFileAs('manuscripts', $file, 'updated_document.docx');

        // Update submission manuscript_file
        $submission->update([
            'manuscript_file' => 'manuscripts/updated_document.docx',
        ]);

        $submission->refresh();

        // Check file has been renamed matching the ID and preserving extension
        $expectedPath = "manuscripts/file-{$submission->id}.docx";
        $this->assertEquals($expectedPath, $submission->manuscript_file);

        // Verify original file is gone and renamed file exists
        $this->assertFalse(Storage::disk('public')->exists('manuscripts/updated_document.docx'));
        $this->assertTrue(Storage::disk('public')->exists($expectedPath));
    }
}

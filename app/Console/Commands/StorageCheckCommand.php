<?php

// File: app/Console/Commands/StorageCheckCommand.php
// Run: php artisan make:command StorageCheckCommand
// Then: php artisan storage:check

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class StorageCheckCommand extends Command
{
    protected $signature = 'storage:check';
    protected $description = 'Check and fix storage link issues';

    public function handle()
    {
        $this->info('=== Storage Diagnostic Check ===');
        
        // 1. Check storage link
        $publicStoragePath = public_path('storage');
        $storageAppPublic = storage_path('app/public');
        
        if (is_link($publicStoragePath)) {
            $this->info('✓ Storage symbolic link exists');
            $linkTarget = readlink($publicStoragePath);
            $this->info("  Link target: {$linkTarget}");
            
            if ($linkTarget === $storageAppPublic) {
                $this->info('✓ Storage link points to correct directory');
            } else {
                $this->error('✗ Storage link points to wrong directory');
                $this->line("  Expected: {$storageAppPublic}");
                $this->line("  Actual: {$linkTarget}");
            }
        } else {
            $this->error('✗ Storage symbolic link does not exist');
            $this->info('Creating storage link...');
            
            // Create directory if not exists
            if (!File::exists($storageAppPublic)) {
                File::makeDirectory($storageAppPublic, 0755, true);
                $this->info('✓ Created storage/app/public directory');
            }
            
            // Create symbolic link
            try {
                $this->call('storage:link');
                $this->info('✓ Storage link created successfully');
            } catch (\Exception $e) {
                $this->error('✗ Failed to create storage link: ' . $e->getMessage());
            }
        }
        
        // 2. Check guru images directory
        $guruImagePath = 'images/guru';
        if (!Storage::disk('public')->exists($guruImagePath)) {
            Storage::disk('public')->makeDirectory($guruImagePath);
            $this->info('✓ Created images/guru directory');
        } else {
            $this->info('✓ images/guru directory exists');
        }
        
        // 3. Test file upload and access
        $testFile = $guruImagePath . '/test.txt';
        Storage::disk('public')->put($testFile, 'test content');
        
        if (Storage::disk('public')->exists($testFile)) {
            $this->info('✓ Can write to storage');
            
            $url = Storage::url($testFile);
            $this->info("✓ Storage URL generated: {$url}");
            
            // Test if file is accessible via web
            $fullPath = public_path('storage/' . $testFile);
            if (File::exists($fullPath)) {
                $this->info('✓ File accessible via web');
            } else {
                $this->error('✗ File not accessible via web');
                $this->line("  Expected path: {$fullPath}");
            }
            
            // Cleanup
            Storage::disk('public')->delete($testFile);
            $this->info('✓ Test file cleaned up');
        } else {
            $this->error('✗ Cannot write to storage');
        }
        
        // 4. Check permissions
        $permissions = substr(sprintf('%o', fileperms($storageAppPublic)), -4);
        $this->info("Storage permissions: {$permissions}");
        
        if ($permissions >= '0755') {
            $this->info('✓ Storage permissions are adequate');
        } else {
            $this->error('✗ Storage permissions may be insufficient');
            $this->line('  Try: chmod -R 755 storage/app/public');
        }
        
        $this->info('=== End Diagnostic ===');
    }
}
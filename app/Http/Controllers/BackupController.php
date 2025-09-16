<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Carbon\Carbon;
use ZipArchive;

class BackupController extends Controller
{
    private $backupPath = 'backups/';
    
    public function __construct()
    {
        // Admin check will be done in each method
    }

    /**
     * Get backup statistics and list
     */
    public function index()
    {
        try {
            // Check if user is authenticated (using sanctum)
            if (!auth('sanctum')->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.'
                ], 401);
            }

            // Check if user is admin
            if (!auth('sanctum')->user()->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. Admin privileges required.'
                ], 403);
            }

            $backups = $this->getBackupList();
            $stats = $this->getBackupStats();
            
            return response()->json([
                'success' => true,
                'backups' => $backups,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            \Log::error('Backup API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching backup data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new backup
     */
    public function create(Request $request)
    {
        try {
            // Check if user is admin
            if (!auth('sanctum')->user()->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. Admin privileges required.'
                ], 403);
            }
            $type = $request->input('type', 'database');
            $backupName = $this->generateBackupName($type);
            
            // Create backup based on type
            switch ($type) {
                case 'database':
                    $result = $this->createDatabaseBackup($backupName);
                    break;
                case 'files':
                    $result = $this->createFilesBackup($backupName);
                    break;
                case 'full':
                    $result = $this->createFullBackup($backupName);
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid backup type'
                    ], 400);
            }

            if ($result['success']) {
                // Store backup info in database
                $this->storeBackupInfo($backupName, $type, $result['size']);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Backup created successfully',
                    'backup' => [
                        'name' => $backupName,
                        'type' => $type,
                        'size' => $result['size'],
                        'path' => $result['path']
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download backup file
     */
    public function download($backupId)
    {
        try {
            // Check if user is admin
            if (!auth('sanctum')->user()->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. Admin privileges required.'
                ], 403);
            }
            $backup = $this->getBackupById($backupId);
            
            if (!$backup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup not found'
                ], 404);
            }

            $filePath = $this->backupPath . $backup['filename'];
            
            if (!Storage::exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup file not found'
                ], 404);
            }

            return Storage::download($filePath, $backup['filename']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error downloading backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore from backup
     */
    public function restore($backupId)
    {
        try {
            // Check if user is admin
            if (!auth('sanctum')->user()->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. Admin privileges required.'
                ], 403);
            }
            $backup = $this->getBackupById($backupId);
            
            if (!$backup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup not found'
                ], 404);
            }

            $filePath = $this->backupPath . $backup['filename'];
            
            if (!Storage::exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup file not found'
                ], 404);
            }

            $result = $this->restoreFromBackup($filePath, $backup['type']);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Backup restored successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error restoring backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete backup
     */
    public function delete($backupId)
    {
        try {
            // Check if user is admin
            if (!auth('sanctum')->user()->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. Admin privileges required.'
                ], 403);
            }
            $backup = $this->getBackupById($backupId);
            
            if (!$backup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup not found'
                ], 404);
            }

            $filePath = $this->backupPath . $backup['filename'];
            
            if (Storage::exists($filePath)) {
                Storage::delete($filePath);
            }

            $this->deleteBackupInfo($backupId);
            
            return response()->json([
                'success' => true,
                'message' => 'Backup deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create database backup
     */
    private function createDatabaseBackup($backupName)
    {
        try {
            $filename = $backupName . '.sql';
            $filePath = $this->backupPath . $filename;
            
            // Get database configuration
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port');
            
            // Create mysqldump command
            $command = sprintf(
                'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg(storage_path('app/' . $filePath))
            );
            
            // Execute command
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0) {
                return [
                    'success' => false,
                    'message' => 'Failed to create database backup'
                ];
            }
            
            $size = Storage::size($filePath);
            
            return [
                'success' => true,
                'path' => $filePath,
                'size' => $this->formatBytes($size)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create files backup
     */
    private function createFilesBackup($backupName)
    {
        try {
            $filename = $backupName . '.zip';
            $filePath = $this->backupPath . $filename;
            $tempPath = storage_path('app/temp/' . $backupName);
            
            // Create temp directory
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0755, true);
            }
            
            // Copy important directories
            $directories = [
                'public/files' => $tempPath . '/files',
                'storage/app/public' => $tempPath . '/storage'
            ];
            
            foreach ($directories as $source => $dest) {
                if (is_dir($source)) {
                    $this->copyDirectory($source, $dest);
                }
            }
            
            // Create a dummy file if no directories exist
            if (!is_dir($tempPath . '/files') && !is_dir($tempPath . '/storage')) {
                file_put_contents($tempPath . '/readme.txt', 'Backup created on ' . date('Y-m-d H:i:s'));
            }
            
            // Create zip file
            $zip = new ZipArchive();
            if ($zip->open(storage_path('app/' . $filePath), ZipArchive::CREATE) === TRUE) {
                $this->addDirectoryToZip($zip, $tempPath, '');
                $zip->close();
            }
            
            // Clean up temp directory
            $this->deleteDirectory($tempPath);
            
            // Check if file exists before getting size
            if (Storage::exists($filePath)) {
                $size = Storage::size($filePath);
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to create backup file'
                ];
            }
            
            return [
                'success' => true,
                'path' => $filePath,
                'size' => $this->formatBytes($size)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create full system backup
     */
    private function createFullBackup($backupName)
    {
        try {
            $filename = $backupName . '.zip';
            $filePath = $this->backupPath . $filename;
            $tempPath = storage_path('app/temp/' . $backupName);
            
            // Create temp directory
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0755, true);
            }
            
            // Create database backup first
            $dbResult = $this->createDatabaseBackup($backupName . '_db');
            if ($dbResult['success']) {
                copy(storage_path('app/' . $dbResult['path']), $tempPath . '/database.sql');
                Storage::delete($dbResult['path']);
            }
            
            // Copy files with better error handling - using absolute paths
            $basePath = base_path();
            $directories = [
                $basePath . '/public/files' => $tempPath . '/files',
                $basePath . '/storage/app/public' => $tempPath . '/storage',
                $basePath . '/public/uploads' => $tempPath . '/uploads',
                $basePath . '/config' => $tempPath . '/config',
                $basePath . '/database/migrations' => $tempPath . '/migrations'
            ];
            
            $filesCopied = 0;
            $copiedFiles = [];
            
            foreach ($directories as $source => $dest) {
                if (is_dir($source)) {
                    $this->copyDirectory($source, $dest);
                    $filesCopied++;
                    $copiedFiles[] = $source;
                }
            }
            
            // Create a backup info file
            $backupInfo = [
                'created_at' => date('Y-m-d H:i:s'),
                'type' => 'full',
                'database_included' => $dbResult['success'] ?? false,
                'directories_copied' => $filesCopied,
                'directories' => $copiedFiles,
                'temp_path' => $tempPath
            ];
            
            file_put_contents($tempPath . '/backup_info.json', json_encode($backupInfo, JSON_PRETTY_PRINT));
            
            // Create zip file with better error handling
            $zip = new ZipArchive();
            $zipResult = $zip->open(storage_path('app/' . $filePath), ZipArchive::CREATE);
            
            if ($zipResult === TRUE) {
                // Add all files from temp directory
                $this->addDirectoryToZip($zip, $tempPath, '');
                $zip->close();
                
                // Verify zip was created and has content
                if (Storage::exists($filePath)) {
                    $size = Storage::size($filePath);
                    if ($size > 0) {
                        // Clean up temp directory
                        $this->deleteDirectory($tempPath);
                        
                        return [
                            'success' => true,
                            'path' => $filePath,
                            'size' => $this->formatBytes($size)
                        ];
                    } else {
                        return [
                            'success' => false,
                            'message' => 'Created zip file is empty'
                        ];
                    }
                } else {
                    return [
                        'success' => false,
                        'message' => 'Failed to create zip file'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to open zip file for writing. Error code: ' . $zipResult
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Restore from backup file
     */
    private function restoreFromBackup($filePath, $type)
    {
        try {
            $tempPath = storage_path('app/temp/restore_' . time());
            
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0755, true);
            }
            
            // Extract backup
            $zip = new ZipArchive();
            if ($zip->open(storage_path('app/' . $filePath)) === TRUE) {
                $zip->extractTo($tempPath);
                $zip->close();
            }
            
            if ($type === 'database' || $type === 'full') {
                // Restore database
                $sqlFile = $tempPath . '/database.sql';
                if (file_exists($sqlFile)) {
                    $this->restoreDatabase($sqlFile);
                }
            }
            
            if ($type === 'files' || $type === 'full') {
                // Restore files
                $this->restoreFiles($tempPath);
            }
            
            // Clean up
            $this->deleteDirectory($tempPath);
            
            return [
                'success' => true,
                'message' => 'Backup restored successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Restore database from SQL file
     */
    private function restoreDatabase($sqlFile)
    {
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        
        $command = sprintf(
            'mysql --host=%s --port=%s --user=%s --password=%s %s < %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($sqlFile)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception('Failed to restore database');
        }
    }

    /**
     * Restore files from backup
     */
    private function restoreFiles($tempPath)
    {
        $directories = [
            $tempPath . '/files' => 'public/files',
            $tempPath . '/storage' => 'storage/app/public',
            $tempPath . '/uploads' => 'public/uploads'
        ];
        
        foreach ($directories as $source => $dest) {
            if (is_dir($source)) {
                $this->copyDirectory($source, $dest);
            }
        }
    }

    /**
     * Get backup list
     */
    private function getBackupList()
    {
        $backups = [];
        $files = Storage::files($this->backupPath);
        
        foreach ($files as $file) {
            $filename = basename($file);
            $backups[] = [
                'id' => md5($file),
                'filename' => $filename,
                'name' => $this->getBackupDisplayName($filename),
                'type' => $this->getBackupType($filename),
                'size' => $this->formatBytes(Storage::size($file)),
                'created_at' => date('Y-m-d H:i:s', Storage::lastModified($file)),
                'status' => 'completed'
            ];
        }
        
        // Sort by creation date (newest first)
        usort($backups, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return $backups;
    }

    /**
     * Get backup statistics
     */
    private function getBackupStats()
    {
        $files = Storage::files($this->backupPath);
        $totalSize = 0;
        $databaseCount = 0;
        $filesCount = 0;
        $lastBackup = null;
        
        foreach ($files as $file) {
            $totalSize += Storage::size($file);
            $filename = basename($file);
            
            if (strpos($filename, '_db') !== false || strpos($filename, 'database') !== false) {
                $databaseCount++;
            } else {
                $filesCount++;
            }
            
            $modified = Storage::lastModified($file);
            if (!$lastBackup || $modified > $lastBackup) {
                $lastBackup = $modified;
            }
        }
        
        return [
            'database' => $databaseCount,
            'files' => $filesCount,
            'lastBackup' => $lastBackup ? date('Y-m-d H:i:s', $lastBackup) : null,
            'totalSize' => $this->formatBytes($totalSize)
        ];
    }

    /**
     * Generate backup name
     */
    private function generateBackupName($type)
    {
        $date = Carbon::now()->format('Y-m-d_H-i-s');
        return $type . '_backup_' . $date;
    }

    /**
     * Get backup display name
     */
    private function getBackupDisplayName($filename)
    {
        $name = str_replace(['_', '.sql', '.zip'], [' ', '', ''], $filename);
        return ucwords($name);
    }

    /**
     * Get backup type from filename
     */
    private function getBackupType($filename)
    {
        if (strpos($filename, 'database') !== false || strpos($filename, '_db') !== false) {
            return 'database';
        } elseif (strpos($filename, 'files') !== false) {
            return 'files';
        } else {
            return 'full';
        }
    }

    /**
     * Get backup by ID
     */
    private function getBackupById($backupId)
    {
        $files = Storage::files($this->backupPath);
        
        foreach ($files as $file) {
            if (md5($file) === $backupId) {
                return [
                    'id' => $backupId,
                    'filename' => basename($file),
                    'type' => $this->getBackupType(basename($file))
                ];
            }
        }
        
        return null;
    }

    /**
     * Store backup info in database (optional)
     */
    private function storeBackupInfo($backupName, $type, $size)
    {
        // You can create a backups table to store this information
        // For now, we'll just log it
        \Log::info("Backup created: {$backupName} ({$type}) - {$size}");
    }

    /**
     * Delete backup info from database (optional)
     */
    private function deleteBackupInfo($backupId)
    {
        // You can implement database cleanup here
        \Log::info("Backup deleted: {$backupId}");
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Copy directory recursively
     */
    private function copyDirectory($src, $dst)
    {
        if (!is_dir($src)) {
            return false;
        }
        
        if (!is_dir($dst)) {
            if (!mkdir($dst, 0755, true)) {
                return false;
            }
        }
        
        $dir = opendir($src);
        if (!$dir) {
            return false;
        }
        
        $filesCopied = 0;
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $srcFile = $src . '/' . $file;
                $dstFile = $dst . '/' . $file;
                
                if (is_dir($srcFile)) {
                    $this->copyDirectory($srcFile, $dstFile);
                } else {
                    if (copy($srcFile, $dstFile)) {
                        $filesCopied++;
                    }
                }
            }
        }
        closedir($dir);
        
        return true;
    }

    /**
     * Add directory to zip recursively
     */
    private function addDirectoryToZip($zip, $dir, $zipDir)
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = scandir($dir);
        $addedFiles = 0;
        
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $filePath = $dir . '/' . $file;
                $zipPath = $zipDir ? $zipDir . '/' . $file : $file;
                
                if (is_dir($filePath)) {
                    // Add directory and its contents
                    $zip->addEmptyDir($zipPath);
                    $this->addDirectoryToZip($zip, $filePath, $zipPath);
                } else {
                    // Add file to zip
                    if (file_exists($filePath) && is_readable($filePath)) {
                        if ($zip->addFile($filePath, $zipPath)) {
                            $addedFiles++;
                        }
                    }
                }
            }
        }
        
        return $addedFiles;
    }

    /**
     * Delete directory recursively
     */
    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        
        rmdir($dir);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminPaymentCollector extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_name',
        'account_holder_name', 
        'account_number',
        'ifsc_code',
        'branch_name',
        'barcode_image',
        'qr_code',
        'is_primary',
        'is_active',
        'notes'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Ensure only one primary payment collector at a time
     */
    protected static function booted()
    {
        static::saving(function ($paymentCollector) {
            if ($paymentCollector->is_primary) {
                // Set all other records to non-primary
                static::where('id', '!=', $paymentCollector->id)
                    ->update(['is_primary' => false]);
            }
        });
    }

    /**
     * Get the primary payment collector
     */
    public static function getPrimary()
    {
        return static::where('is_primary', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get all active payment collectors
     */
    public static function getActive()
    {
        return static::where('is_active', true)
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Mark this collector as primary
     */
    public function markAsPrimary()
    {
        // Set all others to non-primary first
        static::where('id', '!=', $this->id)->update(['is_primary' => false]);
        
        // Set this as primary
        $this->update(['is_primary' => true]);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\AdminPaymentCollector;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AdminPaymentCollectorController extends Controller
{
    /**
     * Display a listing of payment collectors
     */
    public function index(): JsonResponse
    {
        try {
            $paymentCollectors = AdminPaymentCollector::orderBy('is_primary', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $paymentCollectors,
                'message' => 'Payment collectors retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to retrieve payment collectors'
            ], 500);
        }
    }

    /**
     * Store a newly created payment collector
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'bank_name' => 'required|string|max:255',
                'account_holder_name' => 'required|string|max:255',
                'account_number' => 'required|string|max:50',
                'ifsc_code' => 'required|string|max:20',
                'branch_name' => 'nullable|string|max:255',
                'barcode_image' => 'nullable|string', // Base64 image
                'qr_code' => 'nullable|string',
                'is_primary' => 'boolean',
                'notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                    'message' => 'Validation failed'
                ], 422);
            }

            $data = $validator->validated();
            
            // Handle barcode image upload if provided
            if (isset($data['barcode_image']) && !empty($data['barcode_image'])) {
                $barcodeData = $data['barcode_image'];
                if (str_starts_with($barcodeData, 'data:image/')) {
                    // Extract base64 data
                    $barcodeData = substr($barcodeData, strpos($barcodeData, ',') + 1);
                    $barcodeData = base64_decode($barcodeData);
                    
                    // Generate filename
                    $filename = 'payment_barcodes/' . uniqid() . '.png';
                    
                    // Store file
                    Storage::disk('public')->put($filename, $barcodeData);
                    $data['barcode_image'] = $filename;
                }
            }

            $paymentCollector = AdminPaymentCollector::create($data);

            return response()->json([
                'success' => true,
                'data' => $paymentCollector,
                'message' => 'Payment collector created successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to create payment collector'
            ], 500);
        }
    }

    /**
     * Display the specified payment collector
     */
    public function show($id): JsonResponse
    {
        try {
            $paymentCollector = AdminPaymentCollector::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $paymentCollector,
                'message' => 'Payment collector retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Payment collector not found'
            ], 404);
        }
    }

    /**
     * Update the specified payment collector
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $paymentCollector = AdminPaymentCollector::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'bank_name' => 'required|string|max:255',
                'account_holder_name' => 'required|string|max:255',
                'account_number' => 'required|string|max:50',
                'ifsc_code' => 'required|string|max:20',
                'branch_name' => 'nullable|string|max:255',
                'barcode_image' => 'nullable|string',
                'qr_code' => 'nullable|string',
                'is_primary' => 'boolean',
                'is_active' => 'boolean',
                'notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                    'message' => 'Validation failed'
                ], 422);
            }

            $data = $validator->validated();

            // Handle barcode image upload if provided
            if (isset($data['barcode_image']) && !empty($data['barcode_image'])) {
                $barcodeData = $data['barcode_image'];
                if (str_starts_with($barcodeData, 'data:image/')) {
                    // Delete old barcode if exists
                    if ($paymentCollector->barcode_image) {
                        Storage::disk('public')->delete($paymentCollector->barcode_image);
                    }
                    
                    // Extract base64 data
                    $barcodeData = substr($barcodeData, strpos($barcodeData, ',') + 1);
                    $barcodeData = base64_decode($barcodeData);
                    
                    // Generate filename
                    $filename = 'payment_barcodes/' . uniqid() . '.png';
                    
                    // Store file
                    Storage::disk('public')->put($filename, $barcodeData);
                    $data['barcode_image'] = $filename;
                }
            }

            $paymentCollector->update($data);

            return response()->json([
                'success' => true,
                'data' => $paymentCollector->fresh(),
                'message' => 'Payment collector updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to update payment collector'
            ], 500);
        }
    }

    /**
     * Remove the specified payment collector
     */
    public function destroy($id): JsonResponse
    {
        try {
            $paymentCollector = AdminPaymentCollector::findOrFail($id);

            // Delete associated barcode image if exists
            if ($paymentCollector->barcode_image) {
                Storage::disk('public')->delete($paymentCollector->barcode_image);
            }

            $paymentCollector->delete();

            return response()->json([
                'success' => true,
                'message' => 'Payment collector deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to delete payment collector'
            ], 500);
        }
    }

    /**
     * Mark payment collector as primary
     */
    public function markAsPrimary($id): JsonResponse
    {
        try {
            $paymentCollector = AdminPaymentCollector::findOrFail($id);
            $paymentCollector->markAsPrimary();

            return response()->json([
                'success' => true,
                'data' => $paymentCollector->fresh(),
                'message' => 'Payment collector marked as primary successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to mark as primary'
            ], 500);
        }
    }

    /**
     * Toggle active status
     */
    public function toggleStatus($id): JsonResponse
    {
        try {
            $paymentCollector = AdminPaymentCollector::findOrFail($id);
            $paymentCollector->update(['is_active' => !$paymentCollector->is_active]);

            return response()->json([
                'success' => true,
                'data' => $paymentCollector->fresh(),
                'message' => 'Payment collector status updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to update status'
            ], 500);
        }
    }

    /**
     * Get primary payment collector for users
     */
    public function getPrimary(): JsonResponse
    {
        try {
            $primary = AdminPaymentCollector::getPrimary();

            if (!$primary) {
                return response()->json([
                    'success' => false,
                    'message' => 'No primary payment collector found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $primary,
                'message' => 'Primary payment collector retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to retrieve primary payment collector'
            ], 500);
        }
    }
}
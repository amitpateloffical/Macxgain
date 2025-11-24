<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Validator;

class AppSettingController extends Controller
{
    /**
     * Get template setting
     */
    public function getTemplate()
    {
        try {
            $templateId = AppSetting::getValue('selected_template', 'template1');
            return response()->json([
                'success' => true,
                'template_id' => $templateId
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get template: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update template setting
     */
    public function updateTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            AppSetting::setValue('selected_template', $request->template_id);
            
            return response()->json([
                'success' => true,
                'message' => 'Template updated successfully',
                'template_id' => $request->template_id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update template: ' . $e->getMessage()
            ], 500);
        }
    }
}

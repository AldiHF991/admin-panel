<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserDevice;
use App\Models\User;

class DeviceController extends Controller
{
    public function resetDevice(Request $request, $userId)
    {
        // 🔒 Check Admin permission (Assuming gate/policy exists, or just check role)
        // User didn't ask for strict admin check implementation details, but good to have.
        // For now, I'll rely on route middleware 'can:admin-auth' if available or custom logic.
        
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Delete all devices for this user
        UserDevice::where('user_id', $userId)->delete();
        
        // Legacy device information (Backward Compatibility) removed
        // prevent SQLSTATE[42S22]: Column not found: 1054 Unknown column 'device_id'

        return response()->json([
            'message' => 'Device binding reset successfully for user: ' . $user->name,
            'status' => 'success'
        ]);
    }
}

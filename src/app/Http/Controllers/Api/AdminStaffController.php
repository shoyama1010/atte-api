<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminStaffController extends Controller
{
  public function list(Request $request)
  {
    $users = User::query()
      // ->where('role', 'staff')
      ->orderBy('id')
      ->paginate(4);

    return response()->json([
      'data' => $users->getCollection()->map(function (User $user) {
        return [
          'id' => $user->id,
          'name' => $user->name,
          'email' => $user->email,
        ];
      })->values(),
      'current_page' => $users->currentPage(),
      'last_page' => $users->lastPage(),
    ]);
  }
}


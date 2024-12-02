<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;


class UserController extends Controller
{
    public function index(Request $request){
        $keyword = $request->input('keyword');
    
        if ($keyword !== null) {
            $users = User::where('name', 'Like', "%{$keyword}%")
                ->orWhere('kana', 'Like', "%{$keyword}%")
                ->paginate(15);
            $total = $users->total();
        } else {
            $users = User::paginate(15);
            $total = 0;
            $keyword = null;
        }
    
        return view('admin.users.index', compact('users', 'total', 'keyword'));
    }

    public function show(User $user){
        return view('admin.user.show', compact('user'));
    }
    
}

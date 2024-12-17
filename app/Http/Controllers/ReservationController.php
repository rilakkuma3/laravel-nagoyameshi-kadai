<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\User;

class ReservationController extends Controller
{
    // 予約一覧ページ(indexアクション)
    public function index()
    {
        $user = Auth::user();

        $reservations = Reservation::where('user_id', $user->id)->orderBy('reserved_datetime', 'desc')->paginate(15);

        return view('reservations.index', compact('reservations'));        
    }

    // 予約ページ(createアクション)
    public function create(Restaurant $restaurant)
    {
        return view('reservations.create', compact('restaurant'));
    }

    // 予約機能(storeアクション)
    public function store(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            'reservation_date' => 'required|date_format:"Y-m-d"',
            'reservation_time' => 'required|date_format:"H:i"',
            'number_of_people' => 'required|integer|between:1,50'
        ]);

        $reservation = new Reservation();
        $reservation->reserved_datetime = $request->input('reservation_date') . ' ' . $request->input('reservation_time');
        $reservation->number_of_people = $request->input('number_of_people');
        $reservation->user_id = Auth::id();
        $reservation->restaurant_id = $restaurant->id;
        $reservation->save();

        return to_route('reservations.index')->with('flash_message', '予約が完了しました。');
    }

    // 予約キャンセル機能(destroyアクション)
    public function destroy(Reservation $reservation)
    {
        if ($reservation->user_id !== Auth::id()){
            return to_route('reservations.index')->with('error_message', '不正なアクセスです。');
        }

        $reservation->delete();

        return to_route('reservations.index')->with('flash_message', '予約をキャンセルしました。'); 
    }
}

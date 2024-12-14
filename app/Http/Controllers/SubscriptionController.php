<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class SubscriptionController extends Controller
{
    // 有料プラン登録ページ(createアクション)
    public function create()
    {
        $user = Auth::user();

        if ($user->subscribed('premium_plan')) {
            return redirect()->route('subscription.edit');
        }

        $intent = Auth::user()->createSetupIntent();

        return view('subscription.create', compact('intent'));
    }

    // 有料プラン登録機能(storeアクション)
    public function store(Request $request)
    {
        $request->user()->newSubscription(
            'premium_plan', 'price_1Q2OJvCn6kb1TaWJwhjmcYCr'
        )->create($request->paymentMethodId);

        return to_route('home')->with('flash_message', '有料プランへの登録が完了しました。');
    }

    // お支払い方法選択編集ページ(editアクション)
    public function edit()
    {
        $user = Auth::user();
        $intent = Auth::user()->createSetupIntent();

        return view('subscription.edit', compact('user', 'intent'));
    }

    // お支払い方法更新機能(updateアクション)
    public function update(Request $request)
    {
        $request->user()->updateDefaultPaymentMethod($request->paymentMethodId);

        return to_route('home')->with('flash_message', 'お支払い方法を変更しました。');
    }

    // 有料プラン解約ページ(cancelアクション)
    public function cancel()
    {
        return view('subscription.cancel');
    }

    // 有料プラン解約機能(destroyアクション)
    public function destroy(Request $request)
    {
        $request->user()->subscription('premium_plan', 'price_1Q2OJvCn6kb1TaWJwhjmcYCr')->cancelNow();

        return to_route('home')->with('flash_message', '有料プランを解約しました。');
    }
}

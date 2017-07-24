<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Libraries\Helpers\Utility;
use App\Libraries\Payments\Payment;
use App\Libraries\Helpers\Area;
use App\Models\Course;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UserCourse;
use App\Models\OrderAddress;
use App\Models\Discount;
use App\RedisModels\Cart;

class OrderController extends Controller
{
    protected static $fullCart;

    public function editCart()
    {
        $category = Category::select('id', 'slug', 'slug_en')
            ->where('status', Utility::ACTIVE_DB)
            ->where('parent_status', Utility::ACTIVE_DB)
            ->whereNull('parent_id')
            ->orderBy('order', 'desc')
            ->first();

        $cart = self::getFullCart();

        return view('frontend.orders.edit_cart', [
            'cart' => $cart,
            'category' => $category,
        ]);
    }

    public function addCartItem(Request $request)
    {
        if($request->ajax() == false)
            return view('frontend.errors.404');

        $inputs = $request->all();

        $validator = Validator::make($inputs, [
            'course_id' => 'required',
        ]);

        if($validator->passes())
        {
            $course = Course::select('id')
                ->where('status', Course::STATUS_PUBLISH_DB)
                ->where('category_status', Utility::ACTIVE_DB)
                ->where('id', $inputs['course_id'])
                ->first();

            if(empty($course))
                return '';

            $cart = self::getCart();
            $cart->addCartItem($inputs['course_id']);
            $cart->save();

            self::setCookieCartToken($cart->token);

            return view('frontend.orders.cart', [
                'cart' => self::generateFullCart($cart),
            ]);
        }
        else
            return '';
    }

    public function deleteCartItem(Request $request)
    {
        if($request->ajax() == false)
            return view('frontend.errors.404');

        $inputs = $request->all();

        $validator = Validator::make($inputs, [
            'course_id' => 'required',
        ]);

        if($validator->passes())
        {
            $cart = self::getCart();
            $cart->deleteCartItem($inputs['course_id']);

            if(empty($cart->cartItems))
            {
                $cart->delete();

                self::deleteCookieCartToken();

                return 'Empty';
            }
            else
            {
                $cart->save();

                self::setCookieCartToken($cart->token);

                return 'Success';
            }
        }
        else
            return '';
    }

    public function placeOrder(Request $request)
    {
        if($request->isMethod('post'))
        {
            $inputs = $request->all();

            $cart = self::getCart();

            $validator = Validator::make($inputs, [
                'payment_method' => 'required',
            ]);

            $validator->after(function($validator) use(&$inputs, $cart) {
                if(empty($cart->cartItems))
                    $validator->errors()->add('cart', trans('theme.empty_cart'));
                else
                {
                    $userCourses = UserCourse::with(['course' => function($query) {
                        $query->select('id', 'name', 'name_ne');
                    }])->select('course_id')->where('user_id', auth()->user()->id)->whereIn('course_id', $cart->cartItems)->get();

                    $boughtCourses = '';

                    foreach($userCourses as $userCourse)
                    {
                        $cart->deleteCartItem($userCourse->course_id);

                        if($boughtCourses == '')
                            $boughtCourses .= Utility::getValueByLocale($userCourse->course, 'name');
                        else
                            $boughtCourses .= ', ' . Utility::getValueByLocale($userCourse->course, 'name');
                    }

                    if(empty($cart->cartItems))
                    {
                        $cart->delete();

                        self::deleteCookieCartToken();
                    }

                    if($boughtCourses != '')
                        $validator->errors()->add('cart', trans('theme.bought_courses', ['courses' => $boughtCourses]));

                    $paymentMethod = PaymentMethod::select('id', 'name', 'name_en', 'type', 'detail', 'code')
                        ->where('status', Utility::ACTIVE_DB)
                        ->find($inputs['payment_method']);

                    if(empty($paymentMethod))
                        $validator->errors()->add('payment_method', trans('theme.invalid_payment_method'));
                    else
                    {
                        $payment = Payment::getPayments($paymentMethod->code);

                        $inputs['payment'] = $payment;
                        $inputs['order_payment_method'] = $paymentMethod;

                        $payment->validatePlaceOrder($paymentMethod, $inputs, $validator, $cart);
                    }
                }
            });

            if(!$validator->passes())
            {
                $courses = Course::with(['promotionPrice' => function($query) {
                    $query->select('course_id', 'status', 'price', 'start_time', 'end_time');
                }])->select('id', 'name', 'name_en', 'price', 'point_price')
                    ->whereIn('id', $cart->cartItems)
                    ->get();

                $totalPrice = 0;
                $totalPointPrice = 0;

                foreach($courses as $course)
                {
                    if($course->validatePromotionPrice())
                        $totalPrice += $course->promotionPrice->price;
                    else
                        $totalPrice += $course->price;

                    if(!empty($course->point_price))
                        $totalPointPrice += $course->point_price;
                }

                try
                {
                    DB::beginTransaction();

                    $order = new Order();
                    $order->user_id = auth()->user()->id;
                    $order->created_at = date('Y-m-d H:i:s');
                    $order->payment_method_id = $inputs['payment_method'];
                    $order->payment_status = Order::PAYMENT_STATUS_PENDING_DB;
                    $order->total_price = $totalPrice;
                    $order->total_discount_price = 0;
                    $order->total_point_price = $totalPointPrice;

                    if(!empty($inputs['note']))
                        $order->note = $inputs['note'];

                    $order->save();

                    foreach($courses as $course)
                    {
                        $orderItem = new OrderItem();
                        $orderItem->order_id = $order->id;
                        $orderItem->course_id = $course->id;

                        if($course->validatePromotionPrice())
                            $orderItem->price = $course->promotionPrice->price;
                        else
                            $orderItem->price = $course->price;

                        if(empty($course->point_price))
                            $orderItem->point_price = 0;
                        else
                            $orderItem->point_price = $course->point_price;

                        $orderItem->save();
                    }

                    if(isset($inputs['name']))
                    {
                        $orderAddress = new OrderAddress();
                        $orderAddress->order_id = $order->id;
                        $orderAddress->name = $inputs['name'];
                        $orderAddress->email = $inputs['email'];
                        $orderAddress->phone = $inputs['phone'];
                        $orderAddress->address = $inputs['address'];
                        $orderAddress->province = Area::$provinces[$inputs['province']]['name'];
                        $orderAddress->district = Area::$provinces[$inputs['province']]['cities'][$inputs['district']];
                        $orderAddress->save();
                    }

                    DB::commit();

                    $orderThankYou = [
                        'order_number' => $order->number,
                        'payment_method' => Utility::getValueByLocale($inputs['order_payment_method'], 'name'),
                        'total_price' => $order->total_price,
                        'courses' => array(),
                    ];

                    foreach($courses as $course)
                        $orderThankYou['courses'][] = Utility::getValueByLocale($course, 'name');

                    $cart->delete();

                    self::deleteCookieCartToken();

                    return redirect()->action('Frontend\OrderController@thankYou')->with('order', json_encode($orderThankYou));
                }
                catch(\Exception $e)
                {
                    DB::rollBack();

                    return redirect()->action('Frontend\OrderController@placeOrder')->withErrors(['payment_method' => [$e->getMessage()]])->withInput();
                }
            }
            else
                return redirect()->action('Frontend\OrderController@placeOrder')->withErrors($validator)->withInput();
        }

        $cart = self::getFullCart();

        $paymentMethods = PaymentMethod::select('id', 'name', 'name_en', 'type', 'detail', 'code')
            ->where('status', Utility::ACTIVE_DB)
            ->orderBy('order', 'desc')
            ->get();

        return view('frontend.orders.place_order', [
            'cart' => $cart,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function useDiscountCode(Request $request)
    {
        $inputs = $request->all();

        $cart = self::getCart();

        $validator = Validator::make($inputs, [
            'discount_code' => 'required|alpha_num|max:255',
        ]);

        $validator->after(function($validator) use(&$inputs, $cart) {
            if(empty($cart->cartItems))
                $validator->errors()->add('discount_code', trans('theme.empty_cart'));
            else
            {
                $result = Discount::calculateDiscountPrice($inputs['discount_code'], $cart, auth()->user());

                if($result['status'] == 'error')
                    $validator->errors()->add('discount_code', $result['message']);
                else
                    $inputs['discount_price'] = $result['discount'];
            }
        });

        if($validator->passes())
        {
            return json_encode([
                'status' => 'success',
                'discount' => $inputs['discount_price'],
            ]);
        }
        else
            return json_encode([
                'status' => 'error',
                'message' => $validator->errors()->first('discount_code'),
            ]);
    }

    public function thankYou()
    {
        if(session('order'))
        {
            $orderThankYou = json_decode(session('order'), true);

            return view('frontend.orders.thank_you', [
                'orderThankYou' => $orderThankYou,
            ]);
        }
        else
            return redirect()->action('Frontend\OrderController@placeOrder');
    }

    public function getListDistrict(Request $request)
    {
        if($request->ajax() == false)
            return view('frontend.errors.404');

        $inputs = $request->all();

        $validator = Validator::make($inputs, [
            'province_code' => 'required',
        ]);

        if($validator->passes())
        {
            $provinces = Area::$provinces;

            if(isset($provinces[$inputs['province_code']]))
                return json_encode($provinces[$inputs['province_code']]['cities']);
            else
                return '';
        }
        else
            return '';
    }

    protected static function getCart()
    {
        $cart = null;

        $token = self::getCookieCartToken();

        if(!empty($token))
            $cart = Cart::find($token);

        if(empty($cart))
            return new Cart();
        else
            return $cart;
    }

    protected static function generateFullCart($cart)
    {
        $fullCart = [
            'countItem' => 0,
            'totalPrice' => 0,
            'totalPointPrice' => 0,
            'cartItems' => array(),
        ];

        if(!empty($cart->cartItems))
        {
            $courses = Course::with(['promotionPrice' => function($query) {
                $query->select('course_id', 'status', 'price', 'start_time', 'end_time');
            }])->select('id', 'name', 'name_en', 'price', 'point_price', 'image', 'slug', 'slug_en')
                ->whereIn('id', $cart->cartItems)
                ->get();

            $fullCart['countItem'] = count($courses);
            $fullCart['cartItems'] = $courses;

            foreach($fullCart['cartItems'] as $cartItem)
            {
                if($cartItem->validatePromotionPrice())
                    $fullCart['totalPrice'] += $cartItem->promotionPrice->price;
                else
                    $fullCart['totalPrice'] += $cartItem->price;

                if(!empty($cartItem->point_price))
                    $fullCart['totalPointPrice'] += $cartItem->point_price;
            }
        }

        self::$fullCart = $fullCart;

        return $fullCart;
    }

    protected static function getCookieCartToken()
    {
        return request()->cookie(Cart::CART_TOKEN_COOKIE_NAME);
    }

    protected static function setCookieCartToken($token)
    {
        Cookie::queue(Cookie::make(Cart::CART_TOKEN_COOKIE_NAME, $token, Utility::SECOND_ONE_HOUR / 60));
    }

    protected static function deleteCookieCartToken()
    {
        Cookie::queue(Cookie::forget(Cart::CART_TOKEN_COOKIE_NAME));
    }

    public static function getFullCart()
    {
        if(empty(self::$fullCart))
        {
            $cart = self::getCart();

            if(!empty($cart->cartItems))
                $cart->save();

            return self::generateFullCart($cart);
        }
        else
            return self::$fullCart;
    }
}
<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Libraries\Widgets\GridView;
use App\Libraries\Helpers\Html;
use App\Libraries\Helpers\Utility;
use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use App\Models\Profile;

class UserController extends Controller
{
    public function login(Request $request)
    {
        if($request->isMethod('post'))
        {
            $inputs = $request->all();

            $validator = Validator::make($inputs, [
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if($validator->passes())
            {
                $credentials = [
                    'email' => $inputs['email'],
                    'password' => $inputs['password'],
                    'admin' => Utility::ACTIVE_DB,
                    'status' => Utility::ACTIVE_DB,
                ];

                $remember = false;
                if(isset($inputs['remember']))
                    $remember = true;

                if(auth()->attempt($credentials, $remember))
                    return redirect()->action('Backend\HomeController@home');
                else
                    return redirect()->action('Backend\UserController@login')->withErrors(['email' => 'Email or Password is not correct'])->withInput($request->except('password'));
            }
            else
                return redirect()->action('Backend\UserController@login')->withErrors($validator)->withInput($request->except('password'));
        }

        return view('backend.users.login');
    }

    public function logout()
    {
        auth()->logout();

        return redirect()->action('Backend\UserController@login');
    }

    public function adminUser(Request $request)
    {
        $dataProvider = User::with(['profile' => function($query) {
            $query->select('user_id', 'name');
        }])->select('user.id', 'user.username', 'user.email', 'user.status')->where('user.admin', Utility::ACTIVE_DB)->orderBy('user.id', 'desc');

        $inputs = $request->all();

        if(count($inputs) > 0)
        {
            if(!empty($inputs['username']))
                $dataProvider->where('user.username', 'like', '%' . $inputs['username'] . '%');

            if(!empty($inputs['email']))
                $dataProvider->where('user.email', 'like', '%' . $inputs['email'] . '%');

            if(isset($inputs['status']) && $inputs['status'] !== '')
                $dataProvider->where('user.status', $inputs['status']);
        }

        $dataProvider = $dataProvider->paginate(GridView::ROWS_PER_PAGE);

        $columns = [
            [
                'title' => 'Tên Tài Khoản',
                'data' => function($row) {
                    echo Html::a($row->username, [
                        'href' => action('Backend\UserController@editUser', ['id' => $row->id]),
                    ]);
                },
            ],
            [
                'title' => 'Email',
                'data' => 'email',
            ],
            [
                'title' => 'Họ Tên',
                'data' => function($row) {
                    echo $row->profile->name;
                },
            ],
            [
                'title' => 'Vai Trò',
                'data' => function($row) {
                    foreach($row->userRoles as $userRole)
                        echo $userRole->role->name . ' ';
                },
            ],
            [
                'title' => 'Trạng Thái',
                'data' => function($row) {
                    $status = User::getUserStatus($row->status);
                    if($row->status == Utility::ACTIVE_DB)
                        echo Html::span($status, ['class' => 'label label-success']);
                    else
                        echo Html::span($status, ['class' => 'label label-danger']);
                },
            ],
        ];

        $gridView = new GridView($dataProvider, $columns);
        $gridView->setFilters([
            [
                'title' => 'Tên Tài Khoản',
                'name' => 'username',
                'type' => 'input',
            ],
            [
                'title' => 'Email',
                'name' => 'email',
                'type' => 'input',
            ],
            [
                'title' => 'Trạng Thái',
                'name' => 'status',
                'type' => 'select',
                'options' => User::getUserStatus(),
            ],
        ]);
        $gridView->setFilterValues($inputs);

        return view('backend.users.admin_user', [
            'gridView' => $gridView,
        ]);
    }

    public function createUser(Request $request)
    {
        Utility::setBackUrlCookie($request, ['/admin/user?', '/admin/userStudent']);

        $user = new User();
        $user->status = Utility::ACTIVE_DB;
        $user->admin = Utility::INACTIVE_DB;
        $user->collaborator = Utility::INACTIVE_DB;

        if($request->isMethod('post'))
        {
            $inputs = $request->all();

            $validator = Validator::make($inputs, [
                'username' => 'required|alpha_dash|unique:user,username',
                'email' => 'required|email|unique:user,email',
                'password' => 'required|alpha_dash|min:6',
                're_password' => 'required|alpha_dash|min:6|same:password',
            ]);

            if($validator->passes())
            {
                try
                {
                    DB::beginTransaction();

                    $user->username = $inputs['username'];
                    $user->email = $inputs['email'];
                    $user->status = isset($inputs['status']) ? Utility::ACTIVE_DB : Utility::INACTIVE_DB;
                    $user->admin = isset($inputs['admin']) ? Utility::ACTIVE_DB : Utility::INACTIVE_DB;
                    $user->collaborator = isset($inputs['collaborator']) ? Utility::ACTIVE_DB : Utility::INACTIVE_DB;
                    $user->created_at = date('Y-m-d H:i:s');
                    $user->password = Hash::make($inputs['password']);
                    $user->save();

                    $profile = new Profile();
                    $profile->user_id = $user->id;
                    $profile->save();

                    DB::commit();

                    return redirect()->action('Backend\UserController@editUser', ['id' => $user->id])->with('messageSuccess', 'Thành Công');
                }
                catch(\Exception $e)
                {
                    DB::rollBack();

                    return redirect()->action('Backend\UserController@createUser')->withInput()->with('messageError', $e->getMessage());
                }
            }
            else
                return redirect()->action('Backend\UserController@createUser')->withErrors($validator)->withInput();
        }

        return view('backend.users.create_user', [
            'user' => $user,
        ]);
    }

    public function editUser(Request $request, $id)
    {
        Utility::setBackUrlCookie($request, ['/admin/user?', '/admin/userStudent']);

        $user = User::with('userRoles', 'profile')->find($id);

        if(empty($user))
            return view('backend.errors.404');

        if($request->isMethod('post'))
        {
            $inputs = $request->all();

            $validator = Validator::make($inputs, [
                'username' => 'required|alpha_dash|unique:user,username,' . $user->id,
                'email' => 'required|email|unique:user,email,' . $user->id,
                'password' => 'nullable|alpha_dash|min:6',
                're_password' => 'nullable|alpha_dash|min:6|same:password',
                'avatar' => 'mimes:' . implode(',', Utility::getValidImageExt()),
                'first_name' => 'required_with:last_name',
                'phone' => 'nullable|numeric',
                'birthday' => 'nullable|date',
            ]);

            if($validator->passes())
            {
                try
                {
                    DB::beginTransaction();

                    if(isset($inputs['avatar']))
                    {
                        $savePath = User::AVATAR_UPLOAD_PATH . '/' . $user->id;

                        list($imagePath, $imageUrl) = Utility::saveFile($inputs['avatar'], $savePath, Utility::getValidImageExt());

                        if(!empty($imagePath) && !empty($imageUrl))
                        {
                            Utility::resizeImage($imagePath, 200);

                            if(!empty($user->avatar))
                                Utility::deleteFile($user->avatar);

                            $user->avatar = $imageUrl;
                        }
                    }

                    $user->username = $inputs['username'];
                    $user->email = $inputs['email'];
                    $user->status = isset($inputs['status']) ? Utility::ACTIVE_DB : Utility::INACTIVE_DB;
                    $user->admin = isset($inputs['admin']) ? Utility::ACTIVE_DB : Utility::INACTIVE_DB;
                    $user->collaborator = isset($inputs['collaborator']) ? Utility::ACTIVE_DB : Utility::INACTIVE_DB;

                    if(!empty($inputs['password']))
                        $user->password = Hash::make($inputs['password']);

                    $user->save();

                    if(isset($inputs['roles']))
                    {
                        foreach($user->userRoles as $userRole)
                        {
                            $key = array_search($userRole->role_id, $inputs['roles']);

                            if($key !== false)
                                unset($inputs['roles'][$key]);
                            else
                                $userRole->delete();
                        }

                        foreach($inputs['roles'] as $roleId)
                        {
                            $userRole = new UserRole();
                            $userRole->user_id = $user->id;
                            $userRole->role_id = $roleId;
                            $userRole->save();
                        }
                    }
                    else
                    {
                        foreach($user->userRoles as $userRole)
                            $userRole->delete();
                    }

                    $user->profile->first_name = $inputs['first_name'];
                    $user->profile->last_name = $inputs['last_name'];
                    $user->profile->name = (!empty($user->profile->last_name) ? ($user->profile->last_name . ' ') : '') . $user->profile->first_name;
                    $user->profile->gender = $inputs['gender'];
                    $user->profile->birthday = $inputs['birthday'];
                    $user->profile->phone = $inputs['phone'];
                    $user->profile->address = $inputs['address'];
                    $user->profile->description = $inputs['description'];
                    $user->profile->save();

                    DB::commit();

                    return redirect()->action('Backend\UserController@editUser', ['id' => $user->id])->with('messageSuccess', 'Thành Công');
                }
                catch(\Exception $e)
                {
                    DB::rollBack();

                    return redirect()->action('Backend\UserController@editUser', ['id' => $user->id])->withInput()->with('messageError', $e->getMessage());
                }
            }
            else
                return redirect()->action('Backend\UserController@editUser', ['id' => $user->id])->withErrors($validator)->withInput();
        }

        $roles = Role::pluck('name', 'id');

        return view('backend.users.edit_user', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    public function adminUserStudent(Request $request)
    {
        $dataProvider = User::select('id', 'username', 'email', 'status')->where('admin', Utility::INACTIVE_DB)->orderBy('id', 'desc');

        $inputs = $request->all();

        if(count($inputs) > 0)
        {
            if(!empty($inputs['username']))
                $dataProvider->where('username', 'like', '%' . $inputs['username'] . '%');

            if(!empty($inputs['email']))
                $dataProvider->where('email', 'like', '%' . $inputs['email'] . '%');

            if(isset($inputs['status']) && $inputs['status'] !== '')
                $dataProvider->where('status', $inputs['status']);
        }

        $dataProvider = $dataProvider->paginate(GridView::ROWS_PER_PAGE);

        $columns = [
            [
                'title' => 'Tên Tài Khoản',
                'data' => function($row) {
                    echo Html::a($row->username, [
                        'href' => action('Backend\UserController@editUser', ['id' => $row->id]),
                    ]);
                },
            ],
            [
                'title' => 'Email',
                'data' => 'email',
            ],
            [
                'title' => 'Trạng Thái',
                'data' => function($row) {
                    $status = User::getUserStatus($row->status);
                    if($row->status == Utility::ACTIVE_DB)
                        echo Html::span($status, ['class' => 'label label-success']);
                    else
                        echo Html::span($status, ['class' => 'label label-danger']);
                },
            ],
        ];

        $gridView = new GridView($dataProvider, $columns);
        $gridView->setFilters([
            [
                'title' => 'Tên Tài Khoản',
                'name' => 'username',
                'type' => 'input',
            ],
            [
                'title' => 'Email',
                'name' => 'email',
                'type' => 'input',
            ],
            [
                'title' => 'Trạng Thái',
                'name' => 'status',
                'type' => 'select',
                'options' => User::getUserStatus(),
            ],
        ]);
        $gridView->setFilterValues($inputs);

        return view('backend.users.admin_user_student', [
            'gridView' => $gridView,
        ]);
    }

    public function editAccount(Request $request)
    {
        $user = auth()->user();

        if($request->isMethod('post'))
        {
            $inputs = $request->all();

            $validator = Validator::make($inputs, [
                'username' => 'required|alpha_dash|unique:user,username,' . $user->id,
                'email' => 'required|email|unique:user,email,' . $user->id,
                'password' => 'nullable|alpha_dash|min:6',
                're_password' => 'nullable|alpha_dash|min:6|same:password',
                'avatar' => 'mimes:' . implode(',', Utility::getValidImageExt()),
                'first_name' => 'required_with:last_name',
                'phone' => 'nullable|numeric',
                'birthday' => 'nullable|date',
            ]);

            if($validator->passes())
            {
                try
                {
                    DB::beginTransaction();

                    if(isset($inputs['avatar']))
                    {
                        $savePath = User::AVATAR_UPLOAD_PATH . '/' . $user->id;

                        list($imagePath, $imageUrl) = Utility::saveFile($inputs['avatar'], $savePath, Utility::getValidImageExt());

                        if(!empty($imagePath) && !empty($imageUrl))
                        {
                            Utility::resizeImage($imagePath, 200);

                            if(!empty($user->avatar))
                                Utility::deleteFile($user->avatar);

                            $user->avatar = $imageUrl;
                        }
                    }

                    $user->username = $inputs['username'];
                    $user->email = $inputs['email'];

                    if(!empty($inputs['password']))
                        $user->password = Hash::make($inputs['password']);

                    $user->save();

                    $user->profile->first_name = $inputs['first_name'];
                    $user->profile->last_name = $inputs['last_name'];
                    $user->profile->name = (!empty($user->profile->last_name) ? ($user->profile->last_name . ' ') : '') . $user->profile->first_name;
                    $user->profile->gender = $inputs['gender'];
                    $user->profile->birthday = $inputs['birthday'];
                    $user->profile->phone = $inputs['phone'];
                    $user->profile->address = $inputs['address'];
                    $user->profile->description = $inputs['description'];
                    $user->profile->save();

                    DB::commit();

                    return redirect()->action('Backend\UserController@editAccount')->with('messageSuccess', 'Thành Công');
                }
                catch(\Exception $e)
                {
                    DB::rollBack();

                    return redirect()->action('Backend\UserController@editAccount')->withInput()->with('messageError', $e->getMessage());
                }
            }
            else
                return redirect()->action('Backend\UserController@editAccount')->withErrors($validator)->withInput();
        }

        return view('backend.users.edit_account', [
            'user' => $user,
        ]);
    }

    public function autoCompleteUser(Request $request)
    {
        $term = $request->input('term');
        $except = $request->input('except');

        $builder = User::select('user.id', 'user.username', 'user.email', 'profile.name')
            ->join('profile', 'user.id', '=', 'profile.user_id')
            ->where('user.username', 'like', '%' . $term . '%')
            ->orWhere('user.email', 'like', '%' . $term . '%')
            ->orWhere('profile.name', 'like', '%' . $term . '%')
            ->limit(Utility::AUTO_COMPLETE_LIMIT);

        if(!empty($except))
            $builder->where('id', '<>', $except);

        $users = $builder->get()->toJson();

        return $users;
    }
}
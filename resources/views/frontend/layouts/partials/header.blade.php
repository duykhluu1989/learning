<header id="header-1" class="navbar-fixed-top header">
    <div class="menu">
        <nav role="navigation" class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".js-navbar-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a href="{{ action('Frontend\HomeController@home') }}" class="navbar-brand"></a>
                </div>
                <div class="collapse navbar-collapse js-navbar-collapse">
                    <ul class="nav navbar-nav main_menu">

                        @include('frontend.layouts.partials.menu')

                    </ul>
                    <ul class="nav navbar-nav navbar-right lang">
                        <li>
                            <div class="search-button">
                                <a href="#" class="search-toggle" data-selector="#header-1"></a>
                            </div>
                        </li>

                        @if(auth()->guest())

                            <li><a href="#modal_dangky" data-toggle="modal">@lang('theme.sign_up')</a></li>
                            <li><a href="#modal_dangnhap" data-toggle="modal">@lang('theme.sign_in')</a></li>

                        @endif

                        <li>
                            <a href="{{ action('Frontend\HomeController@language', ['locale' => 'vi']) }}"><img src="{{ asset('themes/images/vn.jpg') }}" alt="VN" class="img-responsive"></a>
                        </li>
                        <li>
                            <a href="{{ action('Frontend\HomeController@language', ['locale' => 'en']) }}"><img src="{{ asset('themes/images/en.jpg') }}" alt="EN" class="img-responsive"></a>
                        </li>
                        <li id="CartDetail">

                            @include('frontend.orders.cart', ['cart' => \App\Http\Controllers\Frontend\OrderController::getFullCart()])

                        </li>
                    </ul>
                    <form action="" class="search-box">
                        <input type="text" class="text search-input" placeholder="Type here to search..." />
                    </form>
                </div>
            </div>
        </nav>
    </div>
</header>

@if(auth()->guest())

    <div id="modal_dangky" class="modal fade modal_general" data-backdrop="static" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title text-center">@lang('theme.sign_up') <span class="logo_small"><img src="{{ asset('themes/images/logo_small.png') }}" alt="Logo" class="img-responsive"></span></h4>
                </div>
                <div class="modal-body">
                    <form action="{{ action('Frontend\UserController@register') }}" method="POST" role="form" class="frm_dangky" id="SignUpForm">
                        <div class="form-group">
                            <input type="text" class="form-control" name="name" placeholder="@lang('theme.name')" required="required">
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" name="email" placeholder="Email" required="required">
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control" name="password" placeholder="@lang('theme.password')" required="required">
                        </div>
                        <div class="form-group">
                            <p class="text-center"><a class="btn-link" href="sample.php">Hướng dẫn đăng ký</a></p>
                        </div>
                        <button type="submit" class="btn btn-block btnDangky">@lang('theme.sign_up')</button>
                        <button type="button" class="btn btn-block btnDangnhap SignInWithFacebook"><i class="fa fa-facebook-square" aria-hidden="true"></i>@lang('theme.sign_in_with_facebook')</button>
                        {{ csrf_field() }}
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="modal_dangnhap" class="modal fade modal_general" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title text-center">@lang('theme.sign_in')</h4>
                </div>
                <div class="modal-body">
                    <form action="{{ action('Frontend\UserController@login') }}" method="POST" role="form" class="frm_dangky" id="SignInForm">
                        <div class="form-group">
                            <input type="text" class="form-control" name="email" placeholder="Email" required="required">
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control" name="password" placeholder="@lang('theme.password')" required="required">
                        </div>
                        <button type="submit" class="btn btn-block btnDangky"><i class="fa fa-sign-in" aria-hidden="true"></i>@lang('theme.sign_in')</button>
                        <button type="button" class="btn btn-block btnDangnhap SignInWithFacebook"><i class="fa fa-facebook-square" aria-hidden="true"></i>@lang('theme.sign_in_with_facebook')</button>
                        <div class="form-group">
                            <p class="text-center mt15"><a class="btn-link" href="#modal_quenMK" data-toggle="modal">@lang('theme.forget_password')</a></p>
                        </div>
                        <div class="modal-footer">
                            <p class="text-center"><a href="#modal_dangky" class="btn-link" data-toggle="modal">@lang('theme.not_have_account')</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="modal_quenMK" class="modal fade modal_general" data-backdrop="static" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title text-center">@lang('theme.retrieve_password')</h4>
                </div>
                <div class="modal-body">
                    <p>@lang('theme.retrieve_password_description')</p>
                    <form action="{{ action('Frontend\UserController@retrievePassword') }}" method="POST" role="form" class="frm_dangky" id="ForgetPasswordForm">
                        <div class="form-group">
                            <input type="text" class="form-control" name="email" placeholder="Email" required="required">
                        </div>
                        <button type="submit" class="btn btn-block btnDangky">@lang('theme.retrieve_password')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script type="text/javascript">
            @if(session('needLogin'))
                $(document).ready(function() {
                    $('#modal_dangnhap').modal('show');
                });
            @endif

            $('#SignUpForm').submit(function(e) {
                e.preventDefault();

                var formElem = $(this);

                formElem.find('input').each(function() {
                    $(this).parent().removeClass('has-error').find('span[class="help-block"]').first().remove();
                });

                $.ajax({
                    url: '{{ action('Frontend\UserController@register') }}',
                    type: 'post',
                    data: '_token=' + $('input[name="_token"]').first().val() + '&' + formElem.serialize(),
                    success: function(result) {
                        if(result)
                        {
                            if(result == 'Success')
                            {
                                $('#modal_dangky').modal('hide');

                                swal({
                                    title: '@lang('theme.sign_up_success')',
                                    type: 'success',
                                    allowEscapeKey: false,
                                    showConfirmButton: false
                                });

                                setTimeout(function() {
                                    location.reload();
                                }, 3000);
                            }
                            else
                            {
                                result = JSON.parse(result);

                                for(var name in result)
                                {
                                    if(result.hasOwnProperty(name))
                                    {
                                        formElem.find('input[name="' + name + '"]').first().parent().addClass('has-error').append('' +
                                            '<span class="help-block">' + result[name][0] + '</span>' +
                                        '');
                                    }
                                }
                            }
                        }
                    }
                });
            });

            $('#SignInForm').submit(function(e) {
                e.preventDefault();

                var formElem = $(this);

                formElem.find('input').each(function() {
                    $(this).parent().removeClass('has-error').find('span[class="help-block"]').first().remove();
                });

                $.ajax({
                    url: '{{ action('Frontend\UserController@login') }}',
                    type: 'post',
                    data: '_token=' + $('input[name="_token"]').first().val() + '&' + formElem.serialize(),
                    success: function(result) {
                        if(result)
                        {
                            if(result == 'Success')
                                location.reload();
                            else
                            {
                                result = JSON.parse(result);

                                for(var name in result)
                                {
                                    if(result.hasOwnProperty(name))
                                    {
                                        formElem.find('input[name="' + name + '"]').first().parent().addClass('has-error').append('' +
                                            '<span class="help-block">' + result[name][0] + '</span>' +
                                        '');
                                    }
                                }
                            }
                        }
                    }
                });
            });

            $('#ForgetPasswordForm').submit(function(e) {
                e.preventDefault();

                var formElem = $(this);

                formElem.find('input').each(function() {
                    $(this).parent().removeClass('has-error').find('span[class="help-block"]').first().remove();
                });

                $.ajax({
                    url: '{{ action('Frontend\UserController@retrievePassword') }}',
                    type: 'post',
                    data: '_token=' + $('input[name="_token"]').first().val() + '&' + formElem.serialize(),
                    success: function(result) {
                        if(result)
                        {
                            if(result == 'Success')
                            {
                                $('#modal_quenMK').modal('hide');

                                swal({
                                    title: '@lang('theme.retrieve_password_email')',
                                    type: 'success'
                                });
                            }
                            else
                            {
                                result = JSON.parse(result);

                                for(var name in result)
                                {
                                    if(result.hasOwnProperty(name))
                                    {
                                        formElem.find('input[name="' + name + '"]').first().parent().addClass('has-error').append('' +
                                            '<span class="help-block">' + result[name][0] + '</span>' +
                                        '');
                                    }
                                }
                            }
                        }
                    }
                });
            });

            window.fbAsyncInit = function() {
                FB.init({
                    appId: '{{ \App\Models\Setting::getSettings(\App\Models\Setting::CATEGORY_SOCIAL_DB, \App\Models\Setting::FACEBOOK_APP_ID) }}',
                    cookie: true,
                    xfbml: true,
                    version: '{{ \App\Models\Setting::getSettings(\App\Models\Setting::CATEGORY_SOCIAL_DB, \App\Models\Setting::FACEBOOK_GRAPH_VERSION) }}'
                });

                $('.SignInWithFacebook').click(function() {
                    FB.login(function(response) {
                        if(response.status === 'connected')
                        {
                            $.ajax({
                                url: '{{ action('Frontend\UserController@loginWithFacebook') }}',
                                type: 'post',
                                data: '_token=' + $('input[name="_token"]').first().val() + '&access_token=' + response.authResponse.accessToken,
                                success: function(result) {
                                    if(result)
                                    {
                                        if(result == 'Success')
                                            location.reload();
                                        else
                                        {
                                            swal({
                                                title: result,
                                                type: 'error',
                                                confirmButtonClass: 'btn-success'
                                            });
                                        }
                                    }
                                }
                            });
                        }
                    }, {scope: 'public_profile,email'});
                });
            };
        </script>
    @endpush

@endif
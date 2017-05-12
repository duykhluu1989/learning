<div class="box box-primary">
    <div class="box-header with-border">
        <button type="submit" class="btn btn-primary">{{ empty($role->id) ? 'Tạo Mới' : 'Cập Nhật' }}</button>
        <a href="{{ action('Backend\RoleController@adminRole') }}" class="btn btn-default">Hủy</a>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group{{ $errors->has('name') ? ' has-error': '' }}">
                    <label>Vai Trò</label>
                    <input type="text" class="form-control" name="name" required="required" value="{{ old('name', $role->name) }}" />
                    @if($errors->has('name'))
                        <span class="help-block">{{ $errors->first('name') }}</span>
                    @endif
                </div>
            </div>

            <?php
            $allowedRoutes = array();
            if(!empty($role->permission))
                $allowedRoutes = json_decode($role->permission, true);
            $allowedRoutes = old('permission', $allowedRoutes);

            $routes = Route::getRoutes();
            ?>

            <div class="col-sm-12">
                <div class="form-group">
                    <label>Phân Quyền</label>
                    <div class="row">
                    @foreach($routes as $route)
                        @if(in_array('permission', $route->middleware()))
                            @foreach($route->methods() as $method)
                                @if(strtolower($method) != 'head')
                                    <?php
                                    $routeName = $method . ' - ' . $route->uri();
                                    ?>
                                    <div class="col-sm-3">
                                        <div class="checkbox">
                                            <label>
                                                <input name="permission[]" type="checkbox" value="{{ $routeName }}"<?php echo (in_array($routeName, $allowedRoutes) ? ' checked="checked"' : ''); ?> />{{ $routeName }}
                                            </label>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="box-footer">
        <button type="submit" class="btn btn-primary">{{ empty($role->id) ? 'Tạo Mới' : 'Cập Nhật' }}</button>
        <a href="{{ action('Backend\RoleController@adminRole') }}" class="btn btn-default">Hủy</a>
    </div>
</div>
{{ csrf_field() }}

@if(session('message'))
    @push('scripts')
        <script type="text/javascript">
            alert('{{ session('message') }}');
        </script>
    @endpush
@endif
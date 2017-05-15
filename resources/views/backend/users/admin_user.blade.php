@extends('backend.layouts.main')

@section('page_heading', \App\Models\User::ADMIN_TRUE_LABEL)

@section('section')

    <?php

    $gridView->setTools([
        function() {
            echo \App\Libraries\Helpers\Html::a(\App\Libraries\Helpers\Html::i('', ['class' => 'fa fa-plus fa-fw']), [
                'href' => action('Backend\UserController@createUser'),
                'class' => 'btn btn-primary',
                'data-container' => 'body',
                'data-toggle' => 'popover',
                'data-placement' => 'top',
                'data-content' => 'Thành Viên Mới',
            ]);
        },
    ]);

    $gridView->render();

    ?>

@stop
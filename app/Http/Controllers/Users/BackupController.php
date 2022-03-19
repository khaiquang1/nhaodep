<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Artisan;

class BackupController extends UserController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(

    ) {
        parent::__construct();

        view()->share('type', 'backup');
    }

    public function index()
    {
        $title = trans('backup.backup');

        return view('user.backup.index', compact('title'));
    }

    public function store()
    {
        Artisan::call('backup:run');
        flash(trans('backup.stored_successfully'), 'success');
        return redirect('backup');
    }

    public function clean()
    {
        Artisan::call('backup:clean');
        flash(trans('backup.cleaned_successfully'), 'success');
        return redirect('backup');
    }
}

<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Models\Notification;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class NotificationController extends UserController
{
    /**
     * NotificationController constructor.
     */
    private $userRepository;

    public function __construct(
        UserRepository $userRepository
    )
    {
        parent::__construct();
        $this->userRepository = $userRepository;
    }

    public function getAllData()
    {
        $user_id = $this->userRepository->getUser()->id;
        $user = $this->userRepository->find($user_id);
        $total = $user->notifications()->whereStatus(false)->count();
        $notifications = $user->notifications()->latest()->take(5)->whereStatus(false)->get();

        return response()->json(compact('total', 'notifications'), 200);
    }

    public function postRead(Request $request)
    {
        $this->validate($request, [
            'id' => 'required',
        ]);

        $model = Notification::find($request->get('id'));
        $model->status = true;
        $model->save();

        return response()->json(['message' => 'Notification updated successfully'], 200);
    }
}
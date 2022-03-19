<?php

namespace App\Http\Controllers\Users;

use App\Http\Requests\Request;
use App\Repositories\ProjectsRepository;
use App\Repositories\UserRepository;
use App\Http\Requests\ProjectsRequest;
use App\Http\Controllers\UserController;

class ProjectsController extends UserController
{
    /**
     * @var UserRepositoryProjects
     */
    private $userRepository;

    private $projectsRepository;

    /**
     * TaskController constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository, ProjectsRepository $projectsRepository)
    {
        parent::__construct();

        $this->userRepository = $userRepository;
        $this->projectsRepository = $projectsRepository;

        view()->share('type', 'projects');
    }
    public function index()
    {
        $title = trans('projects.project');
        $users = $this->userRepository->getAllNew()->get()
            ->filter(function ($user) {
                return ($user->inRole('staff') || $user->inRole('admin'));
            })
            ->map(function ($user) {
                return [
                    'name' => $user->full_name .' ( '.$user->email.' )' ,
                    'id' => $user->id
                ];
            })
            ->pluck('name', 'id')->prepend(trans('projects.user'),'');

        return view('user.projects.index', compact('title','users'));
    }

    public function store(TaskRequest $request)
    {
        $task = $this->taskRepository->create($request->except('_token','full_name'));
        return $task->id;
    }


    public function update($task, Request $request)
    {
        $task = $this->taskRepository->find($task);
        $task->update($request->except('_method', '_token'));
    }
    public function create()
    {
        $title = trans('projects.new');
        $this->generateParams();
        return view('user.projects.create', compact('title'));
    }

    public function delete($task)
    {
        $task = $this->taskRepository->find($task);
        $task->delete();

    }

    /**
     * Ajax Data
     */
    public function getAllData()
    {
        $user = $this->userRepository->getUser();
        $projects=$this->projectsRepository->orderBy("id", "ASC")
            ->all()->where('user_id', $user->id)
            ->map(function ($projects) {
                return [
                    'id' => $projects->id,
                    'username' => $projects->user->first_name,
                    'projects_nae' => $projects->name,
                    'date_start' => $projects->date_start,
                    'date_end' => $projects->date_end,
                    "created_at" => $projects->created_at,
                ];
            });
        $events=[];
        foreach ($projects as $d) {
            $event = [];
            $dateFormat = config('settings.date_format');
            $timeFormat = Settings::get('time_format');
            $start_date = Carbon::createFromFormat($dateFormat.' H:i',$d['start_date'])->format('M d Y');
            $end_date = Carbon::createFromFormat($dateFormat.' H:i',$d['end_date'])->addDay()->format('M d Y');
            $event['username'] = $user->firt_name;
            $event['projects_nae'] = $d['name'];
            $event['date_start'] = $d['start_date'];
            $event['date_end'] = $d['end_date'];
            $event['allDay'] = true;
            array_push($events, $event);
        }
        return json_encode($events);

    }
}
